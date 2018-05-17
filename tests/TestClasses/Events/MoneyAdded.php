<?php

namespace Spatie\EventSorcerer\Tests\TestClasses\Events;

use Spatie\EventSorcerer\StoresEvent;
use Spatie\EventSorcerer\ShouldBeStored;
use Spatie\EventSorcerer\Tests\TestClasses\Models\Account;

class MoneyAdded implements ShouldBeStored
{
    use StoresEvent;

    /** @var \Spatie\EventSorcerer\Tests\TestClasses\Models\Account */
    public $account;

    /** @var int */
    public $amount;

    public function __construct(Account $account, int $amount)
    {
        $this->account = $account;

        $this->amount = $amount;
    }
}
