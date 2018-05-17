<?php

namespace Spatie\EventSaucer\Tests\Events;

use Spatie\EventSaucer\StoresEvent;
use Spatie\EventSaucer\ShouldBeStored;
use Spatie\EventSaucer\Tests\Models\Account;

class MoneySubtracted implements ShouldBeStored
{
    use StoresEvent;

    /** @var \Spatie\EventSaucer\Tests\Models\Account */
    public $account;

    /** @var int */
    public $amount;

    public function __construct(Account $account, int $amount)
    {
        $this->account = $account;

        $this->amount = $amount;
    }
}
