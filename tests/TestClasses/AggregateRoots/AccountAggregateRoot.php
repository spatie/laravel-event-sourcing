<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots;

use Spatie\EventSourcing\AggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyMultiplied;

final class AccountAggregateRoot extends AggregateRoot
{
    public int $balance = 0;

    public function addMoney(int $amount): self
    {
        $this->recordThat(new MoneyAdded($amount));

        return $this;
    }

    public function multiplyMoney(int $amount): self
    {
        $this->recordThat(new MoneyMultiplied($amount));

        return $this;
    }

    public function applyMoneyAdded(MoneyAdded $event)
    {
        $this->balance += $event->amount;
    }

    public function applyMoneyMultiplied(MoneyMultiplied $event)
    {
        $this->balance *= $event->amount;
    }
}
