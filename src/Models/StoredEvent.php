<?php

namespace Spatie\EventProjector\Models;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Support\Arrayable;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Exceptions\InvalidStoredEvent;
use Spatie\EventProjector\EventSerializers\EventSerializer;

class StoredEvent implements Arrayable
{
    /** @var int|null */
    public $id;

    /** @var string */
    public $event_properties;

    /** @var string */
    public $aggregate_uuid;

    /** @var string */
    public $event_class;

    /** @var array */
    public $meta_data;

    /** @var \Carbon\Carbon */
    public $created_at;

    /** @var \Spatie\EventProjector\ShouldBeStored|null */
    public $event;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->event_properties = $data['event_properties'];
        $this->aggregate_uuid = $data['aggregate_uuid'];
        $this->event_class = self::getActualClassForEvent($data['event_class']);
        $this->meta_data = $data['meta_data'];
        $this->created_at = $data['created_at'];

        try {
            $this->event = app(EventSerializer::class)->deserialize(
                self::getActualClassForEvent($this->event_class),
                json_encode($this->event_properties)
            );
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
            'event_class' => self::getEventClass($this->event_class),
            'meta_data' => $this->meta_data instanceof Arrayable ? $this->meta_data->toArray() : (array) $this->meta_data,
            'created_at' => $this->created_at,
        ];
    }

    public function handle()
    {
        Projectionist::handleWithSyncProjectors($this);

        if (method_exists($this->event, 'tags')) {
            $tags = $this->event->tags();
        }

        $storedEventJob = call_user_func(
            [config('event-projector.stored_event_job'), 'createForEvent'],
            $this,
            $tags ?? []
        );

        dispatch($storedEventJob->onQueue($this->event->queue ?? config('event-projector.queue')));
    }

    protected static function getActualClassForEvent(string $class): string
    {
        return Arr::get(config('event-projector.event_class_map', []), $class, $class);
    }

    protected static function getEventClass(string $class): string
    {
        $map = config('event-projector.event_class_map', []);

        if (! empty($map) && in_array($class, $map)) {
            return array_search($class, $map, true);
        }

        return $class;
    }
}
