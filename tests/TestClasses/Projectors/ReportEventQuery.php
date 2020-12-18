<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Attributes\Handles;
use Spatie\EventSourcing\EventHandlers\AppliesEvents;
use Spatie\EventSourcing\EventHandlers\Projectors\EventQuery;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;

class ReportEventQuery extends EventQuery
{
    use AppliesEvents;

    private int $money = 0;

    public function __construct(private string $minDate)
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

    #[Handles(MoneyAdded::class)]
    protected function applyMoneyAdded(
        MoneyAdded $moneyAdded
    ) {
        $this->money += $moneyAdded->amount;
    }
}
