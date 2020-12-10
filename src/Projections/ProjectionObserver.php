<?php

namespace Spatie\EventSourcing\Projections;

use Spatie\EventSourcing\Projections\Exceptions\ReadonlyProjection;

class ProjectionObserver
{
    public function updating(Projection $projection): void
    {
        if ($projection->isWriteable()) {
            return;
        }

        $this->preventChanges($projection);
    }

    public function creating(Projection $projection): void
    {
        if ($projection->isWriteable()) {
            return;
        }

        $this->preventChanges($projection);
    }

    public function saving(Projection $projection): void
    {
        if ($projection->isWriteable()) {
            return;
        }

        $this->preventChanges($projection);
    }

    public function deleting(Projection $projection): void
    {
        if ($projection->isWriteable()) {
            return;
        }

        $this->preventChanges($projection);
    }

    private function preventChanges(Projection $projection): void
    {
        throw ReadonlyProjection::new(get_class($projection));
    }
}
