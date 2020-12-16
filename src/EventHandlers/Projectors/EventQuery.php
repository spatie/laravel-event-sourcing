<?php

namespace Spatie\EventSourcing\EventHandlers\Projectors;

use Spatie\EventSourcing\Attributes\Handler;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEventQueryBuilder;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

abstract class EventQuery
{
    public function __construct()
    {
        $this
            ->query(EloquentStoredEvent::query())
            ->cursor()
            ->each(fn (EloquentStoredEvent $event) => $this->apply($event->toStoredEvent()));
    }

    abstract protected function query(EloquentStoredEventQueryBuilder $query): EloquentStoredEventQueryBuilder;

    protected function apply(StoredEvent ...$storedEvents): void
    {
        foreach ($storedEvents as $storedEvent) {
            $this->applyStoredEvent($storedEvent);
        }
    }

    private function applyStoredEvent(StoredEvent $storedEvent)
    {
        Handler::find($storedEvent->event, $this)->each(
            fn (Handler $handler) => $this->{$handler->method}($storedEvent->event)
        );
    }
}
