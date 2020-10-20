<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class AccountAggregateRootWithBeforeApply extends AccountAggregateRoot
{
    public bool $beforeApplyWasCalled = false;

    protected function beforeApply(ShouldBeStored $event): void
    {
        $this->beforeApplyWasCalled = true;
    }
}
