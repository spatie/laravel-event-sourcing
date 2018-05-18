<?php

namespace Spatie\EventProjector\Tests\TestClasses\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;
use Spatie\EventProjector\Tests\TestClasses\Mailables\AccountBroke;

class BrokeReactor
{
    public $handlesEvents = [
        MoneySubtracted::class => 'onMoneySubtracted',
    ];

    public function onMoneySubtracted(MoneySubtracted $event)
    {
        /* TODO: remove the need for refresh */
        if ($event->account->refresh()->isBroke()) {
            Mail::to('john@example.com')->send(new AccountBroke());
        }
    }
}
