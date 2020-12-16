<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Attributes\Handles;
use Spatie\EventSourcing\EventHandlers\Projectors\EventQuery;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;

class ReportProjector extends EventQuery
{
    public int $money = 0;

    public function __construct(string $minDate)
    {
        EloquentStoredEvent::query()
            ->whereEvent(MoneyAdded::class)
            ->whereDate('created_at', '>=', $minDate)
            ->cursor()
            ->each(function (EloquentStoredEvent $eloquentStoredEvent) {
                $this->apply($eloquentStoredEvent->toStoredEvent());
            });
    }

    #[Handles(MoneyAdded::class)]
    protected function applyMoneyAdded(MoneyAdded $moneyAdded)
    {
        $this->money += $moneyAdded->amount;
    }
}
