<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEventWithQueueOverride;

class QueuedProjector extends BalanceProjector implements ShouldQueue
{
    public function onMoneyAddedEventWithQueueOverride(MoneyAddedEventWithQueueOverride $event)
    {
    }
}
