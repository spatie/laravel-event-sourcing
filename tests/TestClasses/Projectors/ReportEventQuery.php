<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Attributes\Handles;
use Spatie\EventSourcing\EventHandlers\Projectors\EventQuery;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEventQueryBuilder;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;

class ReportEventQuery extends EventQuery
{
    public int $money = 0;

    public function __construct(private string $minDate)
    {
        parent::__construct();
    }

    protected function query(EloquentStoredEventQueryBuilder $query): EloquentStoredEventQueryBuilder
    {
        return $query
            ->whereEvent(MoneyAdded::class)
            ->whereDate('created_at', '>=', $this->minDate);
    }

    #[Handles(MoneyAdded::class)]
    protected function applyMoneyAdded(
        MoneyAdded $moneyAdded
    ) {
        $this->money += $moneyAdded->amount;
    }
}
