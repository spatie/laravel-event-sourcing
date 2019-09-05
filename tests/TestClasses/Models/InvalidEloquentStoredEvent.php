<?php

namespace Spatie\EventProjector\Tests\TestClasses\Models;

class InvalidEloquentStoredEvent
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'other_stored_events';
}
