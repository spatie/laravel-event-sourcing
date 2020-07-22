<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository;
use Spatie\EventSourcing\StoredEvents\StoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class ProjectorThatWritesMetaData extends Projector
{
    protected array $handlesEvents = [
        MoneyAddedEvent::class => 'onMoneyAdded',
    ];

    protected $trackStream = '*';

    public function onMoneyAdded(StoredEvent $storedEvent, StoredEventRepository $repository, MoneyAddedEvent $event)
    {
        $storedEvent->meta_data['user_id'] = 1;

        $repository->update($storedEvent);
    }
}
