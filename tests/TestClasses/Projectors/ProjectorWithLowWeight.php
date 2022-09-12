<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\ProjectorWithWeightTestHelper;

class ProjectorWithLowWeight extends Projector
{
    public int $weight = 1;

    public function __construct(
        private ProjectorWithWeightTestHelper $projectorWithWeightTestHelper,
    ) {
    }

    public function onMoneyAdded(MoneyAddedEvent $event): void
    {
        $this->projectorWithWeightTestHelper->calledBy(static::class);
    }
}
