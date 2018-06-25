<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Illuminate\Database\Eloquent\Builder;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;

class BalanceProjector implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
        MoneySubtracted::class => 'onMoneySubtracted',
    ];

    public function onMoneyAdded(MoneyAdded $event)
    {
        $event->account->addMoney($event->amount);
    }

    public function onMoneySubtracted(MoneySubtracted $event)
    {
        $event->account->subtractMoney($event->amount);
    }

    public function onStartingEventReplay()
    {
    }

    public function onFinishedEventReplay()
    {
    }

    public function groupProjectorStatusBy(Builder $query, StoredEvent $storedEvent)
    {
        return [
            'account_id' => $storedEvent->event->account_id,
        ];

        //$query->where('event_properties->account_id', $storedEvent->event->account_id);
        //under the hood ook where met alle event classes waar projector naar luistert

    }
}
