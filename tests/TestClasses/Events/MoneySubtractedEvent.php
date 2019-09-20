<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\EventSourcing\ShouldBeStored;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;

final class MoneySubtractedEvent implements ShouldBeStored
{
    use SerializesModels;

    /** @var \Spatie\EventSourcing\Tests\TestClasses\Models\Account */
    public $account;

    /** @var int */
    public $amount;

    public function __construct(Account $account, int $amount)
    {
        $this->account = $account;

        $this->amount = $amount;
    }
}
