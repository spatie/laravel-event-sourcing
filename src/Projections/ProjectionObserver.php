<?php

namespace Spatie\EventSourcing\Projections;

use Spatie\EventSourcing\Projections\Contracts\Writable;
use Spatie\EventSourcing\Projections\Exceptions\ReadonlyProjection;

class ProjectionObserver
{
    public function updating(Writable $projection): void
    {
        if ($projection->isWriteable()) {
            return;
        }

        $this->preventChanges($projection);
    }

    public function creating(Writable $projection): void
    {
        if ($projection->isWriteable()) {
            return;
        }

        $this->preventChanges($projection);
    }

    public function saving(Writable $projection): void
    {
        if ($projection->isWriteable()) {
            return;
        }

        $this->preventChanges($projection);
    }

    public function deleting(Writable $projection): void
    {
        if ($projection->isWriteable()) {
            return;
        }

        $this->preventChanges($projection);
    }

    private function preventChanges(Writable $projection): void
    {
        throw ReadonlyProjection::new(get_class($projection));
    }
}
