<?php

namespace Spatie\EventProjector\Tests\TestClasses\Repositories;

use Spatie\EventProjector\EloquentStoredEventRepository;
use Spatie\EventProjector\Models\EloquentStoredEvent;
use Spatie\EventProjector\Tests\TestClasses\Models\OtherEloquentStoredEvent;

class OtherEloquentStoredEventRepository extends EloquentStoredEventRepository
{
    protected $storedEventModel;

    public function __construct()
    {
        $this->storedEventModel = OtherEloquentStoredEvent::class;
    }
}
