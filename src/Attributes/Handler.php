<?php

namespace Spatie\EventSourcing\Attributes;

use Illuminate\Support\Collection;
use ReflectionClass;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use ReflectionAttribute;
use ReflectionMethod;

class Handler
{
    public function __construct(
        private object $handler,
        private string $handlerMethod,
    ) {
    }

    public function call(...$arguments): void
    {
        $this->handler->{$this->handlerMethod}(...$arguments);
    }

    /**
     * @param \Spatie\EventSourcing\StoredEvents\ShouldBeStored $event
     * @param object $handler
     *
     * @return \Illuminate\Support\Collection|\Spatie\EventSourcing\Attributes\Handler[]
     */
    public static function find(ShouldBeStored $event, object $handler): Collection
    {
        $handlerClass = new ReflectionClass($handler);

        return collect($handlerClass->getMethods(ReflectionMethod::IS_PUBLIC))
            ->mapWithKeys(fn(ReflectionMethod $reflectionMethod) => [$reflectionMethod->getName() => $reflectionMethod->getAttributes(Handles::class)])
            ->filter(function (array $handleAttributes) use ($event) {
                return ! is_null(
                    collect($handleAttributes)
                        ->map(fn(ReflectionAttribute $attribute) => $attribute->newInstance())
                        ->first(fn(Handles $handlesAttribute) => $handlesAttribute->handles($event))
                );
            })
            ->keys()
            ->map(fn (string $handlerMethod) => new Handler($handler, $handlerMethod));
    }
}
