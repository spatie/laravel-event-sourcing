<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots;

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Partials\MoneyPartial;

class AccountAggregateRootWithPartial extends AggregateRoot
{
    public int $aggregateVersion = 0;

    public int $aggregateVersionAfterReconstitution = 0;

    public $dependency;

    protected Math $math;

    protected MoneyPartial $moneyPartial;

    public function __construct(Math $math, $dependency = null)
    {
        $this->dependency = $dependency;
        $this->math = $math;

        $this->moneyPartial = new MoneyPartial($this, $math);
    }

    public function addMoney(int $amount): self
    {
        $this->moneyPartial->addMoney($amount);

        return $this;
    }

    public function multiplyMoney(int $amount): self
    {
        $this->moneyPartial->multiplyMoney($amount);

        return $this;
    }

    public function getBalance()
    {
        return $this->moneyPartial->balance;
    }
}
