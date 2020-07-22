<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;

class MoneyAddedEventWithQueueOverride extends ShouldBeStored
{
    use SerializesModels;

    public object $account;

    public int $amount;

    /** @var string */
    public $queue = 'testQueue';

    public function __construct(Account $account, int $amount)
    {
        $this->account = $account;

        $this->amount = $amount;
    }

    public function tags(): array
    {
        return [
            'Account:'.$this->account->id,
            self::class,
        ];
    }
}
