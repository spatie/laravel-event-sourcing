<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;

class EmptyAccountEvent extends ShouldBeStored
{
    public Account $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }
}
