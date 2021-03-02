<?php

namespace Spatie\EventSourcing\Commands;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class HandledBy
{
    public function __construct(
        public string $handlerClass
    ) {
    }
}
