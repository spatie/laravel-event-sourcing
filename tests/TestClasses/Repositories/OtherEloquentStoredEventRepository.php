<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Repositories;

use Spatie\EventSourcing\EloquentStoredEventRepository;
use Spatie\EventSourcing\Tests\TestClasses\Models\OtherEloquentStoredEvent;

class OtherEloquentStoredEventRepository extends EloquentStoredEventRepository
{
    protected $storedEventModel;

    public function __construct()
    {
        $this->storedEventModel = OtherEloquentStoredEvent::class;
    }
}
