<?php

namespace Spatie\EventSourcing;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use ReflectionClass;
use Spatie\EventSourcing\Attributes\EventAlias;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class Dictionary
{
    public function getAliasDictionary() : Collection
    {
        /** @var LazyCollection<string,string> $aliasedClasses */
        $aliasedClasses = LazyCollection::make(function () {
            foreach (get_declared_classes() as $declaredClass) {
                if (is_subclass_of($declaredClass, ShouldBeStored::class)) {
                    yield $declaredClass;
                }
            }
        })
            ->map(fn (string $eventClass) => new ReflectionClass($eventClass))
            ->reject(fn (ReflectionClass $reflection) => empty($reflection->getAttributes(EventAlias::class)))
            ->mapWithKeys(function (ReflectionClass $reflection) {
                /** @var ReflectionClass<EventAlias> $attribute */
                $attribute = $reflection->getAttributes(EventAlias::class)[0];

                return [$reflection->getName() => ($attribute->newInstance())->alias];
            });

        return collect(array_flip(config('event-sourcing.event_class_map', [])))
            ->merge($aliasedClasses->toArray())
            ->flip();
    }

    public function getAliasFromClass(string $class): string|false
    {
        return $this->getAliasDictionary()->search($class, true);
    }

    public function getClassFromAlias(string $alias): string|false
    {
        return $this->getAliasDictionary()->get($alias, false);
    }
}
