<?php

namespace Spatie\EventProjector;

use Exception;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\EventHandlers\EventHandler;
use Spatie\EventProjector\Events\FinishedEventReplay;
use Spatie\EventProjector\Events\StartingEventReplay;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;
use Spatie\EventProjector\EventHandlers\EventHandlerCollection;
use Spatie\EventProjector\Events\EventHandlerFailedHandlingEvent;
use Spatie\EventProjector\Events\ProjectorDidNotHandlePriorEvents;

class Projectionist
{
    /** @var array */
    protected $config;

    /** @var \Spatie\EventProjector\EventHandlers\EventHandlerCollection */
    protected $projectors;

    /** @var \Spatie\EventProjector\EventHandlers\EventHandlerCollection */
    protected $reactors;

    /** @var bool */
    protected $isReplaying = false;

    public function __construct(array $config)
    {
        $this->projectors = new EventHandlerCollection();

        $this->reactors = new EventHandlerCollection();

        $this->config = $config;
    }

    public function addProjector($projector): Projectionist
    {
        if (is_string($projector)) {
            $projector = app($projector);
        }

        if (! $projector instanceof Projector) {
            throw InvalidEventHandler::notAProjector($projector);
        }

        $this->projectors->add($projector);

        return $this;
    }

    public function addProjectors(array $projectors): Projectionist
    {
        foreach ($projectors as $projector) {
            $this->addProjector($projector);
        }

        return $this;
    }

    public function getProjectors(): Collection
    {
        return $this->projectors->all();
    }

    public function getProjector(string $name): ?Projector
    {
        return $this->projectors->all()->first(function (Projector $projector) use ($name) {
            return $projector->getName() === $name;
        });
    }

    public function addReactor($reactor): Projectionist
    {
        $this->reactors->add($reactor);

        return $this;
    }

    public function addReactors(array $reactors): Projectionist
    {
        foreach ($reactors as $reactor) {
            $this->addReactor($reactor);
        }

        return $this;
    }

    public function getReactors(): Collection
    {
        return $this->reactors->all();
    }

    public function storeEvent(ShouldBeStored $event)
    {
        $storedEvent = $this->getStoredEventClass()::createForEvent($event);

        $this->handleImmediately($storedEvent);

        dispatch(new HandleStoredEventJob($storedEvent))
            ->onQueue($this->config['queue']);
    }

    public function handle(StoredEvent $storedEvent)
    {
        $this->applyStoredEventToProjectors(
            $storedEvent,
            $this->projectors->forEvent($storedEvent)
        );

        $this->applyStoredEventToReactors(
            $storedEvent,
            $this->reactors->forEvent($storedEvent)
        );
    }

    public function handleImmediately(StoredEvent $storedEvent)
    {
        $projectors = $this->projectors
            ->forEvent($storedEvent)
            ->filter(function (Projector $projector) {
                return $projector->shouldBeCalledImmediately();
            });

        $this->applyStoredEventToProjectors($storedEvent, $projectors);
    }

    protected function applyStoredEventToProjectors(StoredEvent $storedEvent, Collection $projectors)
    {
        foreach ($projectors as $projector) {
            if ($projector->hasAlreadyReceivedEvent($storedEvent)) {
                continue;
            }

            if (! $projector->hasReceivedAllPriorEvents($storedEvent)) {
                event(new ProjectorDidNotHandlePriorEvents($projector, $storedEvent));

                $projector->markAsNotUpToDate($storedEvent);

                continue;
            }

            if (! $this->callEventHandler($projector, $storedEvent)) {
                continue;
            }

            $projector->rememberReceivedEvent($storedEvent);
        }
    }

    protected function applyStoredEventToReactors(StoredEvent $storedEvent, Collection $reactors)
    {
        foreach ($reactors as $reactor) {
            $this->callEventHandler($reactor, $storedEvent);
        }
    }

    protected function callEventHandler(EventHandler $eventHandler, StoredEvent $storedEvent): bool
    {
        try {
            $eventHandler->handle($storedEvent);
        } catch (Exception $exception) {
            if (! $this->config['catch_exceptions']) {
                throw $exception;
            }

            $eventHandler->handleException($exception);

            event(new EventHandlerFailedHandlingEvent($eventHandler, $storedEvent, $exception));

            return false;
        }

        return true;
    }

    public function isReplaying(): bool
    {
        return $this->isReplaying;
    }

    public function replay(Collection $projectors, int $afterStoredEventId = 0, callable $onEventReplayed = null)
    {
        $this->isReplaying = true;

        $projectors = new EventHandlerCollection($projectors);

        event(new StartingEventReplay($projectors->all()));

        $projectors->call('onStartingEventReplay');

        $this->getStoredEventClass()::query()
            ->after($afterStoredEventId ?? 0)
            ->chunk($this->config['replay_chunk_size'], function (Collection $storedEvents) use ($projectors, $onEventReplayed) {
                $storedEvents->each(function (StoredEvent $storedEvent) use ($projectors, $onEventReplayed) {
                    $this->applyStoredEventToProjectors(
                        $storedEvent,
                        $projectors->forEvent($storedEvent)
                    );

                    if ($onEventReplayed) {
                        $onEventReplayed($storedEvent);
                    }
                });
            });

        $this->isReplaying = false;

        event(new FinishedEventReplay());

        $projectors->call('onFinishedEventReplay');
    }

    protected function getStoredEventClass(): string
    {
        return config('event-projector.stored_event_model');
    }
}
