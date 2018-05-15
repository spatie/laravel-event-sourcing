<?php

namespace Spatie\EventSaucer\Tests\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\EventSaucer\StoresEvent;
use Spatie\EventSaucer\ShouldBeStored;
use Spatie\EventSaucer\Tests\Models\Account;

class MoneyAdded implements ShouldBeStored
{
    use StoresEvent, SerializesModels;

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