<?php

namespace Spatie\EventProjector\Tests;

use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ProjectorThatWritesMetaData;

class ProjectorTest extends TestCase
{
    /** @test */
    public function it_can_reach_the_stored_event_and_write_meta_data_to_it()
    {
        EventProjectionist::addProjector(ProjectorThatWritesMetaData::class);

        event(new MoneyAdded(Account::create(), 1234));

        $this->assertCount(1, StoredEvent::get());

        $this->assertEquals(1, StoredEvent::first()->meta_data->get('user_id'));
    }
}
