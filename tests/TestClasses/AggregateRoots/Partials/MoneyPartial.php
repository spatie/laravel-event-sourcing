<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Partials;

use Spatie\EventSourcing\AggregateRoots\AggregatePartial;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Math;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyMultiplied;

class MoneyPartial extends AggregatePartial
{
    public int $balance = 0;

    protected Math $math;

    public function __construct(AggregateRoot $aggregateRoot, Math $math)
    {
        parent::__construct($aggregateRoot);
        $this->math = $math;
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

    public function applyMoneyMultiplied(MoneyMultiplied $event)
    {
        $this->balance = $this->math->multiply($this->balance, $event->amount);
    }

}
