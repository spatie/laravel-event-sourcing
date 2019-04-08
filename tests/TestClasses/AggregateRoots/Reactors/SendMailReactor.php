<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventProjector\EventHandlers\EventHandler;
use Spatie\EventProjector\EventHandlers\HandlesEvents;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\Mailable\MoneyAddedMailable;

final class SendMailReactor implements EventHandler
{
    use HandlesEvents;

    protected $handleEvent = MoneyAdded::class;

    public function __invoke(MoneyAdded $event, string $aggregateUuid)
    {
        Mail::to('john@example.com')->send(new MoneyAddedMailable($event->amount, $aggregateUuid));
    }
}
