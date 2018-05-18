<?php

namespace Spatie\EventProjector\Tests\TestClasses\Events;

use Spatie\EventProjector\StoresEvent;
use Spatie\EventProjector\ShouldBeStored;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;

class MoneySubtracted implements ShouldBeStored
{
    use StoresEvent;

    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    public $account;

    /** @var int */
    public $amount;

    public function __construct(Account $account, int $amount)
    {
        $this->account = $account;

        $this->amount = $amount;
    }
}
