<?php

namespace Spatie\EventSourcing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class EventSerializer
{
    public function __construct(
        public string $serializerClass
    ) {
    }
}
