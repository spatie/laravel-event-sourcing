<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\StoredEvent;
use Spatie\EventProjector\StoredEventRepository;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;

final class ProjectorThatWritesMetaData implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAddedEvent::class => 'onMoneyAdded',
    ];

    protected $trackStream = '*';

    public function onMoneyAdded(StoredEvent $storedEvent, StoredEventRepository $repository, MoneyAddedEvent $event)
    {
        $storedEvent->meta_data['user_id'] = 1;

        $repository->update($storedEvent);
    }
}
