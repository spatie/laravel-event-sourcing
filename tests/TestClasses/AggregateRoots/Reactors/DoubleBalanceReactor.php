<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Reactors;

use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;

class DoubleBalanceReactor extends Reactor
{
    public function onMoneyAdded(MoneyAdded $event)
    {
        /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
        $aggregateRoot = AccountAggregateRoot::retrieve($event->aggregateRootUuid());

        $aggregateRoot->multiplyMoney(2)->persist();
    }
}
