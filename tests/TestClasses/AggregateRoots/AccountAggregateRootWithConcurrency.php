<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots;

use Exception;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyMultiplied;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyRemoved;

class AccountAggregateRootWithConcurrency extends AggregateRoot
{
    public int $balance = 0;

    protected Math $math;

    public function __construct(Math $math)
    {
        $this->math = $math;
    }

    public function addMoney(int $amount): self
    {
        $this->recordConcurrently(new MoneyAdded($amount));

        return $this;
    }

    public function addMoneyNonConcurrent(int $amount): self
    {
        $this->recordConcurrently(new MoneyAdded($amount), false);

        return $this;
    }

    protected function applyMoneyAdded(MoneyAdded $event)
    {
        $this->balance += $event->amount;
    }

    public function removeMoney(int $amount): self
    {
        $this
            ->recordConcurrently(
                new MoneyRemoved($amount),
                function (self $aggregateRootWithConcurrency) use ($amount) {
                    if ($aggregateRootWithConcurrency->balance < $amount) {
                        throw new Exception('Insufficient balance');
                    }
                },
            );

        return $this;
    }

    protected function applyMoneyRemoved(MoneyRemoved $event)
    {
        $this->balance -= $event->amount;
    }

    public function multiplyMoney(int $amount): self
    {
        $this->recordThat(new MoneyMultiplied($amount));

        return $this;
    }

    public function applyMoneyMultiplied(MoneyMultiplied $event)
    {
        $this->balance = $this->math->multiply($this->balance, $event->amount);
    }
}
