<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots;

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyMultiplied;

class AccountAggregateRoot extends AggregateRoot
{
    public int $balance = 0;

    public int $aggregateVersion = 0;

    public int $aggregateVersionAfterReconstitution = 0;

    public $dependency;

    public function __construct($dependency = null)
    {
        $this->dependency = $dependency;
    }

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

    protected function applyMoneyAdded(MoneyAdded $event)
    {
        $this->balance += $event->amount;
    }

    public function applyMoneyMultiplied(MoneyMultiplied $event, Math $math)
    {
        $this->balance = $math->multiply($this->balance, $event->amount);
    }
}
