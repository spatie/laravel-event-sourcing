<?php

namespace Spatie\EventSourcerer\Tests\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcerer\Tests\Events\MoneySubtracted;
use Spatie\EventSourcerer\Tests\Mailables\AccountBroke;

class BrokeReactor
{
    public $handlesEvents = [
        MoneySubtracted::class => 'onMoneySubtracted',
    ];

    public function onMoneySubtracted(MoneySubtracted $event)
    {
        //dd('here', $event->account->refresh()->getAttributes());
        if ($event->account->refresh()->isBroke()) {
            Mail::to('john@example.com')->send(new AccountBroke());
        }
    }
}
