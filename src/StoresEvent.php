<?php

namespace Spatie\EventSaucer;

use Illuminate\Queue\SerializesModels;

trait StoresEvent
{
    use SerializesModels;
}