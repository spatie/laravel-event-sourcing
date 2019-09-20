<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\StoredEvent;
use Spatie\EventSourcing\Projectors\Projector;
use Spatie\EventSourcing\StoredEventRepository;
use Spatie\EventSourcing\Projectors\ProjectsEvents;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

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
