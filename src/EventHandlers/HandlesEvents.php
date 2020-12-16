<?php

namespace Spatie\EventSourcing\EventHandlers;

use Exception;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Spatie\EventSourcing\Attributes\Handles;
use Spatie\EventSourcing\Exceptions\InvalidEventHandler;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

trait HandlesEvents
{
    public function handles(): array
    {
        return $this->getEventHandlingMethods()->keys()->toArray();
    }

    public function handle(StoredEvent $storedEvent)
    {
        $eventClass = $storedEvent->event_class;

        $handlerClassOrMethod = $this->getEventHandlingMethods()->get($eventClass);

        if (class_exists($handlerClassOrMethod) && method_exists($handlerClassOrMethod, '__invoke')) {
            $handler = app($handlerClassOrMethod);

            return $handler($storedEvent->event);
        }

        if (! method_exists($this, $handlerClassOrMethod)) {
            throw InvalidEventHandler::eventHandlingMethodDoesNotExist(
                $this,
                $storedEvent->event,
                $handlerClassOrMethod,
            );
        }

        $this->{$handlerClassOrMethod}($storedEvent->event);
    }

    public function handleException(Exception $exception): void
    {
        report($exception);
    }

    public function getEventHandlingMethods(): Collection
    {
        if (! isset($this->handlesEvents) && ! isset($this->handleEvent)) {
            return $this->autoDetectHandlesEvents();
        }

        $handlesEvents = collect($this->handlesEvents ?? [])
            ->mapWithKeys(function (string $handlerMethod, $eventClass) {
                if (is_numeric($eventClass)) {
                    return [$handlerMethod => 'on' . ucfirst(class_basename($handlerMethod))];
                }

                return [$eventClass => $handlerMethod];
            });

        if ($this->handleEvent ?? false) {
            $handlesEvents->put($this->handleEvent, get_class($this));
        }

        return $handlesEvents;
    }

    private function autoDetectHandlesEvents(): Collection
    {
        return collect((new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->flatMap(function (ReflectionMethod $method) {
                $method = new ReflectionMethod($this, $method->name);

                $listensToAttributes = $method->getAttributes(Handles::class);

                return empty($listensToAttributes)
                    ? $this->determineAcceptedEventsFromTypeHint($method)
                    : $this->determineAcceptedEventsFromAttribute($method);
            })
            ->mapWithKeys(fn(array $items) => $items)
            ->filter();
    }

    private function determineAcceptedEventsFromTypeHint(ReflectionMethod $method): ?array
    {
        $eventClass = collect($method->getParameters())
            ->map(fn(ReflectionParameter $parameter) => optional($parameter->getType())->getName())
            ->first(fn($typeHint) => is_subclass_of($typeHint, ShouldBeStored::class));

        if (! $eventClass) {
            return null;
        }

        return [
            [$eventClass => $method->name],
        ];
    }

    private function determineAcceptedEventsFromAttribute(ReflectionMethod $method): array
    {
        return collect($method->getAttributes(Handles::class))
            ->map(fn(ReflectionAttribute $attribute) => $attribute->newInstance())
            ->flatMap(fn(Handles $handlesAttribute) => $handlesAttribute->eventClasses)
            ->map(fn (string $eventClass) => [$eventClass => $method->getName()])
            ->toArray();
    }
}
