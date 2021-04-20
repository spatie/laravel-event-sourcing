<?php

namespace Spatie\EventSourcing\AggregateRoots;

class FakeAggregateRootForPartial extends AggregateRoot
{
    public function __construct()
    {
        // Do nothing
    }

    public function addPartial(AggregatePartial $aggregatePartial): void
    {
        $this->resolvePartials();

        $this->entities[] = $aggregatePartial;
    }
}
