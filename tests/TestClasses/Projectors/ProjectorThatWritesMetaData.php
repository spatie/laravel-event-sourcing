<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;

class ProjectorThatWritesMetaData implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
    ];

    protected $trackStream = '*';

    public function onMoneyAdded(StoredEvent $storedEvent, MoneyAdded $event)
    {
        $metaData = $storedEvent['meta_data'];

        $metaData['user_id'] = 1;

        $storedEvent->meta_data = $metaData;

        $storedEvent->save();
    }
}
