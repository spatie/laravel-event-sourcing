<?php

namespace Spatie\EventSourcing\Tests\TestClasses\EventSerializer;

use Spatie\EventSourcing\EventSerializers\JsonEventSerializer;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class DummySerializer extends JsonEventSerializer
{
    public function serialize(ShouldBeStored $event): string
    {
        return '{"message":"message set by custom serializer"}';
    }
}
