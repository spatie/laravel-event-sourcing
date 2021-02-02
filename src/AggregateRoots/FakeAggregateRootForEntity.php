<?php

namespace Spatie\EventSourcing\AggregateRoots;

class FakeAggregateRootForEntity extends AggregateRoot
{
    public function __construct()
    {
        // Do nothing
    }

    public function addEntity(AggregateEntity $aggregateEntity): void
    {
        $this->entities[] = $aggregateEntity;
    }
}
