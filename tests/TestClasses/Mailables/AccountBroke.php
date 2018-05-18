<?php

namespace Spatie\EventProjector\Tests\TestClasses\Mailables;

use Illuminate\Mail\Mailable;

class AccountBroke extends Mailable
{
    public function build()
    {
        return $this;
    }
}
