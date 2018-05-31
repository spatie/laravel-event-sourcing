<?php

namespace Spatie\EventProjector\Tests\TestClasses\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventProjector\EventHandler\EventHandler;
use Spatie\EventProjector\EventHandler\HandlesEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;
use Spatie\EventProjector\Tests\TestClasses\Mailables\AccountBroke;

class BrokeReactor implements EventHandler
{
    use HandlesEvents;

    public $handlesEvents = [
        MoneySubtracted::class => 'onMoneySubtracted',
    ];

    public function onMoneySubtracted(MoneySubtracted $event)
    {
        if ($event->account->isBroke()) {
            Mail::to('john@example.com')->send(new AccountBroke());
        }
    }
}
