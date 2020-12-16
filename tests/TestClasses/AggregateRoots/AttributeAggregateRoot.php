<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots;

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\Attributes\Handles;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;

class AttributeAggregateRoot extends AggregateRoot
{
    public bool $applied = false;

    public function addMoney(int $amount): self
    {
        $this->recordThat(new MoneyAdded($amount));

        return $this;
    }

    #[Handles(MoneyAdded::class)]
    protected function applyingWithAttribute()
    {
        $this->applied = true;
    }
}
