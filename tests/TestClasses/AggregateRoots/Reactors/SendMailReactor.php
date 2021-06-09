<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\EventHandlers\HandlesEvents;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Mailable\MoneyAddedMailable;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;

class SendMailReactor implements EventHandler
{
    use HandlesEvents;

    protected string $handleEvent = MoneyAdded::class;

    public function __invoke(MoneyAdded $event)
    {
        Mail::to('john@example.com')->send(new MoneyAddedMailable($event->amount, $event->aggregateRootUuid()));
    }
}
