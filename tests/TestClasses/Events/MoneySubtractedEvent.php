<?php

namespace Spatie\EventProjector\Tests\TestClasses\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\EventProjector\ShouldBeStored;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;

final class MoneySubtractedEvent implements ShouldBeStored
{
    use SerializesModels;

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
