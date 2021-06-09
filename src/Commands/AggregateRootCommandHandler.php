<?php

namespace Spatie\EventSourcing\Commands;

class AggregateRootCommandHandler
{
    public function __construct(
        private string $aggregateRootClass,
        private string $aggregateUuid,
    ) {
    }

    public function __invoke(object $command): void
    {
        /** @var \Spatie\EventSourcing\AggregateRoots\AggregateRoot|string $aggregateRootClass */
        $aggregateRootClass = $this->aggregateRootClass;

        $aggregateRoot = $aggregateRootClass::retrieve($this->aggregateUuid);

        $aggregateRoot->handleCommand($command)->persist();
    }
}
