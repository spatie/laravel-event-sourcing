<?php

namespace Spatie\EventSaucer\Tests\Mailables;

use Illuminate\Mail\Mailable;

class AccountBroke extends Mailable
{
    public function build()
    {
        return $this;
    }
}