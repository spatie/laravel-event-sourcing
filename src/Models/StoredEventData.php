<?php

namespace Spatie\EventProjector\Models;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Spatie\EventProjector\EventSerializers\EventSerializer;
use Spatie\EventProjector\Exceptions\InvalidStoredEvent;
use Spatie\EventProjector\Facades\Projectionist;

class StoredEventData implements Arrayable
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
        $this->event_class = $data['event_class'];
        $this->meta_data = $data['meta_data'];
        $this->created_at = $data['created_at'];

        try {
            $this->event = app(EventSerializer::class)->deserialize(
                $this->event_class,
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
            'event_class' => $this->event_class,
            'meta_data' => $this->meta_data instanceof Arrayable ? $this->meta_data->toArray() : (array) $this->meta_data,
            'created_at' => $this->created_at,
        ];
    }

    public function handle() {
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
}
