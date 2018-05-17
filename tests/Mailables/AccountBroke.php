<?php

namespace Spatie\EventSorcerer\Tests\Mailables;

use Illuminate\Mail\Mailable;

class AccountBroke extends Mailable
{
    public function build()
    {
        return $this;
    }
}
