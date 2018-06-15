<?php

namespace Spatie\EventProjector\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Projectors\Projector;

class ProjectorStatus extends Model
{
    public $guarded = [];

    public static function getForProjector(Projector $projector, StoredEvent $storedEvent = null): self
    {
        $attributes = ['projector_name' => $projector->getName()];

        if ($projector->streamBased()) {
            $attributes += [
                'stream_name' => $storedEvent->stream_name,
                'stream_id' => $storedEvent->stream_id,
            ];
        }

        return self::firstOrCreate($attributes);
    }

    public function rememberLastProcessedEvent(StoredEvent $storedEvent): self
    {
        $this->last_processed_event_id = $storedEvent->id;

        $attributes = [
            'last_processed_event_id' => $storedEvent->id,
        ];

        if ($this->getProjector()->streamBased()) {
            $attributes += [
                'stream_name' => $storedEvent->stream_name,
                'stream_id' => $storedEvent->stream_id,
            ];
        }

        $this->update($attributes);

        return $this;
    }

    public static function hasReceivedAllEvents(Projector $projector): bool
    {
        $highestEventId = (int) ProjectorStatus::query()
            ->where('projector_name', $projector->getName())
            ->max('last_processed_event_id') ?? 0;

        return $highestEventId === StoredEvent::getMaxId();
    }

    public function getProjector(): Projector
    {
        return EventProjectionist::getProjector($this->projector_name);
    }
}
