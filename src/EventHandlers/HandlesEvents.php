<?php

namespace Spatie\EventSourcing\EventHandlers;

use Exception;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Spatie\EventSourcing\Attributes\ListensTo;
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

        $parameters = [
            'event' => $storedEvent->event,
            'storedEvent' => $storedEvent,
            'aggregateUuid' => $storedEvent->aggregate_uuid,
        ];

        if (class_exists($handlerClassOrMethod)) {
            return app()->call([app($handlerClassOrMethod), '__invoke'], $parameters);
        }

        if (! method_exists($this, $handlerClassOrMethod)) {
            throw InvalidEventHandler::eventHandlingMethodDoesNotExist(
                $this,
                $storedEvent->event,
                $handlerClassOrMethod,
            );
        }

        app()->call([$this, $handlerClassOrMethod], $parameters);
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

                $listensToAttributes = $method->getAttributes(ListensTo::class);

                return empty($listensToAttributes)
                    ? $this->determineAcceptedEventsFromTypeHint($method)
                    : $this->determineAcceptedEventsFromAttribute($method);
            })
            ->mapWithKeys(fn (array $items) => $items)
            ->filter();
    }

    private function determineAcceptedEventsFromTypeHint(ReflectionMethod $method): ?array
    {
        $eventClass = collect($method->getParameters())
            ->map(fn (ReflectionParameter $parameter) => optional($parameter->getType())->getName())
            ->first(fn ($typeHint) => is_subclass_of($typeHint, ShouldBeStored::class));

        if (! $eventClass) {
            return null;
        }

        return [
            [$eventClass => $method->name],
        ];
    }

    private function determineAcceptedEventsFromAttribute(ReflectionMethod $method): array
    {
        return collect($method->getAttributes(ListensTo::class))
            ->map(fn (ReflectionAttribute $attribute) => $attribute->newInstance())
            ->map(fn (ListensTo $listensTo) => [$listensTo->eventClass => $method->name])
            ->toArray();
    }
}
