<?php

namespace Spatie\EventSourcing\Tests;

use Carbon\Carbon;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ReportEventQuery;

class EventQueryTest extends TestCase
{
    /** @test */
    public function test_apply_from_the_past()
    {
        Carbon::setTestNow('2019-01-01');

        event(new MoneyAdded(10));

        Carbon::setTestNow('2020-01-01');

        event(new MoneyAdded(10));
        event(new MoneyAdded(20));

        $report = new ReportEventQuery('2020-01-01');

        $this->assertEquals(30, $report->money());
    }
}
