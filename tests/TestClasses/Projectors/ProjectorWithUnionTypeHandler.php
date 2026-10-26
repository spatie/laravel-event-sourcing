<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\TestEvent;

class ProjectorWithUnionTypeHandler extends Projector
{
    public static array $log = [];

    public function onMoneyChanged(MoneyAddedEvent|MoneySubtractedEvent $event): void
    {
        self::$log[] = [$event::class, $event->amount];
    }

    public function onMoneyAdded(MoneyAddedEvent $event): void
    {
        self::$log[] = ['added', $event->amount];
    }

    public function onTestEvent(TestEvent $event): void
    {
        self::$log[] = [$event::class];
    }
}
