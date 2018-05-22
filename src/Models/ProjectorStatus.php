<?php

namespace Spatie\EventProjector\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\EventProjector\Projectors\Projector;

class ProjectorStatus extends Model
{
    public $guarded = [];

    public static function getForProjector(Projector $projector): ProjectorStatus
    {
        return ProjectorStatus::firstOrCreate(['projector_name' => $projector->getName()]);
    }

    public function rememberLastProcessedEvent(StoredEvent $storedEvent): self
    {
        $this->last_processed_event_id = $storedEvent->id;

        $this->save();

        return $this;
    }
}
