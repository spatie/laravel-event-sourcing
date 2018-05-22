<?php

namespace Spatie\EventProjector\EventSerializers;

use Spatie\EventProjector\ShouldBeStored;

interface Serializer
{
    public function serialize(ShouldBeStored $event): string;

    public function deserialize(string $eventClass, string $json): ShouldBeStored;

}