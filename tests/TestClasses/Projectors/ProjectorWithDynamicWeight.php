<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\StoredEvents\StoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\ProjectorWithWeightTestHelper;

class ProjectorWithDynamicWeight extends Projector
{
    public function __construct(
        private ProjectorWithWeightTestHelper $projectorWithWeightTestHelper,
    ) {
    }

    public function getWeight(?StoredEvent $event): int
    {
        return match ($event?->event_class) {
            MoneyAddedEvent::class => 2,
            MoneySubtractedEvent::class => -2,
            default => 0,
        };
    }

    public function onMoneyAdded(MoneyAddedEvent $event): void
    {
        $this->projectorWithWeightTestHelper->calledBy(static::class);
    }

    public function onMoneySubtracted(MoneySubtractedEvent $event): void
    {
        $this->projectorWithWeightTestHelper->calledBy(static::class);
    }
}
