<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\Reactors\AsyncReactor as AsyncReactorInterface;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Mailables\AccountBroke;

class AsyncReactor extends BrokeReactor implements AsyncReactorInterface
{

    public function onMoneySubtracted(MoneySubtractedEvent $event)
    {
        Mail::to('john@example.com')->send(new AccountBroke());
    }
}
