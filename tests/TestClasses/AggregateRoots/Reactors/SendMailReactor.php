<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\EventHandlers\HandlesEvents;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Mailable\MoneyAddedMailable;

final class SendMailReactor implements EventHandler
{
    use HandlesEvents;

    protected $handleEvent = MoneyAdded::class;

    public function __invoke(MoneyAdded $event, string $aggregateUuid)
    {
        Mail::to('john@example.com')->send(new MoneyAddedMailable($event->amount, $aggregateUuid));
    }
}
