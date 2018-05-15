<?php

namespace Spatie\EventSaucer\Tests\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\EventSaucer\LogsEvent;
use Spatie\EventSaucer\Tests\Models\Account;


class MoneyAdded implements ShouldBeLogged
{
    use LogsEvent, SerializesModels;

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