<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots;

use Spatie\EventProjector\AggregateRoot;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Repositories\OtherEloquentStoredEventRepository;

final class AccountAggregateRootWithStoredEventSpecified extends AggregateRoot
{
    public $balance = 0;
    public $storedEventRepository = OtherEloquentStoredEventRepository::class;

    public function addMoney(int $amount): self
    {
        $this->recordThat(new MoneyAdded($amount));

        return $this;
    }

    public function applyMoneyAdded(MoneyAdded $event)
    {
        $this->balance += $event->amount;
    }
}
