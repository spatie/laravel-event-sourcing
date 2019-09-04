<?php

namespace Spatie\EventProjector\Tests\TestClasses\Repositories;

use Spatie\EventProjector\EloquentStoredEventRepository;
use Spatie\EventProjector\Tests\TestClasses\Models\OtherEloquentStoredEvent;

class OtherEloquentStoredEventRepository extends EloquentStoredEventRepository
{
    protected $storedEventModel = OtherEloquentStoredEvent::class;
}
