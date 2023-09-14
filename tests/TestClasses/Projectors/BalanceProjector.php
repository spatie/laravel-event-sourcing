<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;

class BalanceProjector extends Projector
{
    public static array $log = [];

    public function onMoneyAdded(MoneyAddedEvent $event)
    {
        static::$log[] = $event::class;
        $event->account->addMoney($event->amount);
    }

    public function onMoneySubtracted(MoneySubtractedEvent $event)
    {
        static::$log[] = $event::class;
        $event->account->subtractMoney($event->amount);
    }

    public function onStartingEventReplay(?string $aggregateUuid = null)
    {
        static::$log[] = 'onStartingEventReplay';
    }

    public function onFinishedEventReplay(?string $aggregateUuid = null)
    {
        static::$log[] = 'onFinishedEventReplay';
    }
}
