<?php

namespace Spatie\EventSourcerer\Tests\Events;

use Spatie\EventSourcerer\StoresEvent;
use Spatie\EventSourcerer\ShouldBeStored;
use Spatie\EventSourcerer\Tests\Models\Account;

class MoneySubtracted implements ShouldBeStored
{
    use StoresEvent;

    /** @var \Spatie\EventSourcerer\Tests\Models\Account */
    public $account;

    /** @var int */
    public $amount;

    public function __construct(Account $account, int $amount)
    {
        $this->account = $account;

        $this->amount = $amount;
    }
}
