<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\EventHandlers\HandlesEvents;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Mailables\AccountBroke;

final class BrokeReactor implements EventHandler
{
    use HandlesEvents;

    protected $handlesEvents = [
        MoneySubtractedEvent::class => 'onMoneySubtracted',
    ];

    public function onMoneySubtracted(MoneySubtractedEvent $event)
    {
        if ($event->account->isBroke()) {
            Mail::to('john@example.com')->send(new AccountBroke());
        }
    }
}
