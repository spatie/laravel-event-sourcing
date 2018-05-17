<?php

namespace Spatie\EventSorcerer;

use Illuminate\Queue\SerializesModels;

trait StoresEvent
{
    use SerializesModels;
}
