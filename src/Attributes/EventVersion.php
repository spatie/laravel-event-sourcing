<?php

namespace Spatie\EventSourcing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class EventVersion
{
    public function __construct(
        public int $version
    ) {
    }
}
