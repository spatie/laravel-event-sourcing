<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class ProjectorThatWritesMetaData extends Projector
{
    protected $trackStream = '*';

    public function __construct(
        protected StoredEventRepository $repository
    ) {
    }

    public function onMoneyAdded(MoneyAddedEvent $event)
    {
        $storedEvent = $this->repository->find($event->storedEventId());

        $storedEvent->meta_data['user_id'] = 1;

        $this->repository->update($storedEvent);
    }
}
