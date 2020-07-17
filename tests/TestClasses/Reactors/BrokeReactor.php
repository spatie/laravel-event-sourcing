<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\EventHandlers\HandlesEvents;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Mailables\AccountBroke;

class BrokeReactor extends Reactor
{
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
