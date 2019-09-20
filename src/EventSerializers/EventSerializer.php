<?php

namespace Spatie\EventSourcing\EventSerializers;

use Spatie\EventSourcing\ShouldBeStored;

interface EventSerializer
{
    public function serialize(ShouldBeStored $event): string;

    public function deserialize(string $eventClass, string $json): ShouldBeStored;
}
