<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Models;

use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent as BaseStoredEvent;

class OtherEloquentStoredEvent extends BaseStoredEvent
{
    protected $table = 'other_stored_events';
}
