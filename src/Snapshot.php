<?php

namespace Spatie\EventSourcing;

class Snapshot
{
    public string $aggregateUuid;

    public int $aggregateVersion;

    /** @var mixed */
    public $state;

    public function __construct(
        string $aggregateUuid,
        int $aggregateVersion,
        $state
    ) {
        $this->aggregateUuid = $aggregateUuid;
        $this->aggregateVersion = $aggregateVersion;
        $this->state = $state;
    }
}
