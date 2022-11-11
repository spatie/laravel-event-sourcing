<?php

namespace Spatie\EventSourcing\Tests;

use Carbon\Carbon;

use function PHPUnit\Framework\assertEquals;

use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ReportEventQuery;

it('should apply from the past', function () {
    Carbon::setTestNow('2019-01-01');

    event(new MoneyAdded(10));

    Carbon::setTestNow('2020-01-01');

    event(new MoneyAdded(10));
    event(new MoneyAdded(20));

    $report = new ReportEventQuery('2020-01-01');

    assertEquals(30, $report->money());
});
