<?php

namespace Spatie\EventSourcing\StoredEvents;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Spatie\EventSourcing\EventSerializers\EventSerializer;
use Spatie\EventSourcing\Exceptions\InvalidStoredEvent;
use Spatie\EventSourcing\Facades\Projectionist;

class StoredEvent implements Arrayable
{
    public ?int $id;

    /** @var array|string */
    public $event_properties;

    public string $aggregate_uuid;

    public string $aggregate_version;

    public string $event_class;

    /** @var array|string */
    public $meta_data;

    public string $created_at;

    public ?ShouldBeStored $event;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->event_properties = $data['event_properties'];
        $this->aggregate_uuid = $data['aggregate_uuid'];
        $this->aggregate_version = $data['aggregate_version'];
        $this->event_class = self::getActualClassForEvent($data['event_class']);
        $this->meta_data = $data['meta_data'];
        $this->created_at = $data['created_at'];

        try {
            $this->event = app(EventSerializer::class)->deserialize(
                self::getActualClassForEvent($this->event_class),
                is_string($this->event_properties)
                    ? $this->event_properties
                    : json_encode($this->event_properties),
                is_string($this->meta_data)
                    ? $this->meta_data
                    : json_encode($this->meta_data),
            );

            $this->event->setMetaData(optional($this->meta_data)->toArray());
        } catch (Exception $exception) {
            throw InvalidStoredEvent::couldNotUnserializeEvent($this, $exception);
        }
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'event_properties' => $this->event_properties,
            'aggregate_uuid' => $this->aggregate_uuid,
            'aggregate_version' => $this->aggregate_version,
            'event_class' => self::getEventClass($this->event_class),
            'meta_data' => $this->meta_data instanceof Arrayable ? $this->meta_data->toArray() : (array) $this->meta_data,
            'created_at' => $this->created_at,
        ];
    }

    public function handle()
    {
        Projectionist::handleWithSyncEventHandlers($this);

        if (method_exists($this->event, 'tags')) {
            $tags = $this->event->tags();
        }

        if (! $this->shouldDispatchJob()) {
            return;
        }

        $storedEventJob = call_user_func(
            [config('event-sourcing.stored_event_job'), 'createForEvent'],
            $this,
            $tags ?? []
        );

        dispatch($storedEventJob->onQueue($this->event->queue ?? config('event-sourcing.queue')));
    }

    protected function shouldDispatchJob(): bool
    {
        /** @var \Spatie\EventSourcing\EventHandlers\EventHandlerCollection $eventHandlers */
        $eventHandlers = Projectionist::allEventHandlers();

        return $eventHandlers->asyncEventHandlers()->count() > 0;
    }

    protected static function getActualClassForEvent(string $class): string
    {
        return Arr::get(config('event-sourcing.event_class_map', []), $class, $class);
    }

    protected static function getEventClass(string $class): string
    {
        $map = config('event-sourcing.event_class_map', []);

        if (! empty($map) && in_array($class, $map)) {
            return array_search($class, $map, true);
        }

        return $class;
    }
}
