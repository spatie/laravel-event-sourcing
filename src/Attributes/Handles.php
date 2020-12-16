<?php

namespace Spatie\EventSourcing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Handles
{
    public array $eventClasses;

    public function __construct(
        string ...$eventClasses,
    ) {
        $this->eventClasses = $eventClasses;
    }

    public function handles(string|object $handler): bool
    {
        if (is_object($handler)) {
            $handler = $handler::class;
        }

        return in_array($handler, $this->eventClasses);
    }
}
