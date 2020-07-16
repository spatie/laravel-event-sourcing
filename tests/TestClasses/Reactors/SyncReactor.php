<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\EventHandlers\HandlesEvents;
use Spatie\EventSourcing\Reactors\SyncReactor as SyncReactorInterface;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Mailables\AccountBroke;

class SyncReactor implements SyncReactorInterface
{
    use HandlesEvents;

    public function onMoneySubtracted(MoneySubtractedEvent $event)
    {
        Mail::to('john@example.com')->send(new AccountBroke());
    }
}
