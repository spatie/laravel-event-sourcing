<?php

namespace Spatie\EventSaucer\Tests\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventSaucer\Tests\Events\MoneySubtracted;
use Spatie\EventSaucer\Tests\Mailables\AccountBroke;

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