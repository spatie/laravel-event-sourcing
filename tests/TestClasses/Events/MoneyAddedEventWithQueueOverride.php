<?php

namespace Spatie\EventProjector\Tests\TestClasses\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\EventProjector\ShouldBeStored;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;

final class MoneyAddedEventWithQueueOverride implements ShouldBeStored
{
    use SerializesModels;

    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    public $account;

    /** @var int */
    public $amount;

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
