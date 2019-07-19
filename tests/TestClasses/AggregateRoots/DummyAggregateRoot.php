<?php

namespace Spatie\EventProjector\Tests\TestClasses;

use Spatie\EventProjector\AggregateRoot;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\StorableEvents\DummyEvent;

class DummyAggregateRoot extends AggregateRoot
{
    /** @var int */
    private $latestInteger = 0;

    public function dummy()
    {
        $this->recordThat(new DummyEvent($this->latestInteger + 1));
    }

    public function applyDummyEvent(DummyEvent $dummyEvent)
    {
        $this->latestInteger = $dummyEvent->integer;
    }
}

