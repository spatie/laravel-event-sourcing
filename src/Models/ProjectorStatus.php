<?php

namespace Spatie\EventProjector\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Facades\EventProjectionist;

class ProjectorStatus extends Model
{
    public $guarded = [];

    public $casts = [
        'has_received_all_prior_events' => 'boolean',
    ];

    public static function getForProjector(Projector $projector, string $stream = 'main'): ProjectorStatus
    {
        return self::firstOrCreate([
            'projector_name' => $projector->getName(),
            'stream' => $stream,
        ]);
    }

    public static function getAllForProjector(Projector $projector): Collection
    {
        return static::where('projector_name', $projector->getName())->get();
    }

    public function rememberLastProcessedEvent(StoredEvent $storedEvent): ProjectorStatus
    {
        $this->last_processed_event_id = $storedEvent->id;
        $this->save();

        return $this;
    }

    public static function hasReceivedAllEvents(Projector $projector): bool
    {
        $highestEventId = (int) self::query()
            ->where('projector_name', $projector->getName())
            ->max('last_processed_event_id') ?? 0;

        return $highestEventId === StoredEvent::getMaxId();
    }

    public function getProjector(): Projector
    {
        return EventProjectionist::getProjector($this->projector_name);
    }
}
