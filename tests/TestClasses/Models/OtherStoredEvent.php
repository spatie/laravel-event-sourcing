<?php

namespace Spatie\EventProjector\Tests\TestClasses\Models;

use Spatie\EventProjector\Models\StoredEvent as BaseStoredEvent;

class OtherStoredEvent extends BaseStoredEvent
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'other_stored_events';
}
