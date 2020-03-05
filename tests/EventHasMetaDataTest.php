<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\DummyAggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors\GetMetaDataProjector;

class EventHasMetaDataTest extends TestCase
{
    /** @test */
    public function it_can_store_the_aggregate_root_uuid_on_the_event_so_the_project_can_get_it()
    {
        Projectionist::addProjector(GetMetaDataProjector::class);

        $aggregateRoot = DummyAggregateRoot::retrieve('my-uuid');

        $aggregateRoot->dummy();

        $aggregateRoot->persist();

        $this->assertEquals('my-uuid', GetMetaDataProjector::$foundAggregateRootUuid);
    }
}
