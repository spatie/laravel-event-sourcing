<?php

namespace Spatie\EventProjector;

use Illuminate\Queue\SerializesModels;

trait StoresEvent
{
    use SerializesModels;
}
