<?php

namespace Spatie\EventSourcing\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE|Attribute::TARGET_METHOD)]
class ListensTo
{
    public function __construct(
        public string $eventName,
    )
    {
    }
}
