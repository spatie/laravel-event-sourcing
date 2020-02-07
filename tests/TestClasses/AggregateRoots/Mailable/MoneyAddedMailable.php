<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Mailable;

use Illuminate\Mail\Mailable;

class MoneyAddedMailable extends Mailable
{
    public int $amount;

    public string $aggregateUuid;

    public function __construct(int $amount, string $aggregateUuid)
    {
        $this->amount = $amount;

        $this->aggregateUuid = $aggregateUuid;
    }

    public function build()
    {
        return $this->html('');
    }
}
