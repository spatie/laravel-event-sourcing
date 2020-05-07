<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots;

use Exception;
use Illuminate\Support\LazyCollection;

class AccountAggregateRootWithFailingPersist extends AccountAggregateRoot
{
    public function persistWithoutApplyingToEventHandlers(): LazyCollection
    {
        throw new Exception("Failing to persist");
    }
}
