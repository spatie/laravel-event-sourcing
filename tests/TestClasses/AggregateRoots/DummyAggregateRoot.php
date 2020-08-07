<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots;

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\DummyEvent;

class DummyAggregateRoot extends AggregateRoot
{
    private int $latestInteger = 0;

    public function dummy()
    {
        $this->recordThat(new DummyEvent($this->latestInteger + 1));
    }

    protected function applyDummyEvent(DummyEvent $dummyEvent)
    {
        $this->latestInteger = $dummyEvent->integer;
    }

    public function getLatestInteger(): int
    {
        return $this->latestInteger;
    }
}
