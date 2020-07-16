<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\Reactors\Reactor;
use Spatie\EventSourcing\Reactors\ReactsEvents;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Mailables\AccountBroke;

class BrokeReactor implements Reactor
{
    use ReactsEvents;

    protected array $handlesEvents = [
        MoneySubtractedEvent::class => 'onMoneySubtracted',
    ];

    public function onMoneySubtracted(MoneySubtractedEvent $event)
    {
        if ($event->account->isBroke()) {
            Mail::to('john@example.com')->send(new AccountBroke());
        }
    }
}
