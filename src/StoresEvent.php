<?php

namespace Spatie\EventSourcerer;

use Illuminate\Queue\SerializesModels;

trait StoresEvent
{
    use SerializesModels;
}
