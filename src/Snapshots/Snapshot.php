<?php

namespace Spatie\EventSourcing\Snapshots;

class Snapshot
{
    public function __construct(
        public string $aggregateUuid,
        public int $aggregateVersion,
        public $state
    ) {
    }
}
