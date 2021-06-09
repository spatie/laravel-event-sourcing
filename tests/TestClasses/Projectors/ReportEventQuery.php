<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\EventQuery;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;

class ReportEventQuery extends EventQuery
{
    protected int $money = 0;

    public function __construct(protected string $minDate)
    {
        EloquentStoredEvent::query()
            ->whereEvent(MoneyAdded::class)
            ->whereDate('created_at', '>=', $this->minDate)
            ->cursor()
            ->each(fn (EloquentStoredEvent $eloquentStoredEvent) => $this->apply($eloquentStoredEvent->toStoredEvent()));
    }

    public function money(): int
    {
        return $this->money;
    }

    protected function applyMoneyAdded(
        MoneyAdded $moneyAdded
    ) {
        $this->money += $moneyAdded->amount;
    }
}
