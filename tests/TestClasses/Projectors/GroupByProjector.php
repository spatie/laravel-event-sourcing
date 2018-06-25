<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;

class GroupByProjector implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
    ];

    public function onMoneyAdded(MoneyAdded $event)
    {
        $event->account->addMoney($event->amount);
    }

    public function resetState()
    {
        Account::truncate();
    }

    public function groupProjectorStatusBy(StoredEvent $storedEvent): array
    {
        return [
            'account.id' => $storedEvent->event->account->id,
        ];
    }
}
