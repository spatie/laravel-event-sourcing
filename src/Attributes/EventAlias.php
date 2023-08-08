<?php

namespace Spatie\EventSourcing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class EventAlias
{
    public function __construct(
        public string $alias
    ) {
    }
}
