<?php

namespace Spatie\EventProjector\Tests\TestClasses\Models;

use Spatie\EventProjector\Models\EloquentStoredEvent as BaseStoredEvent;

class OtherEloquentStoredEvent extends BaseStoredEvent
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'other_stored_events';
}
