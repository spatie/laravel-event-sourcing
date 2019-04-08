<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots\Mailable;

use Illuminate\Mail\Mailable;

final class MoneyAddedMailable extends Mailable
{
    /** @var int */
    public $amount;

    /** @var string */
    public $aggregateUuid;

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
