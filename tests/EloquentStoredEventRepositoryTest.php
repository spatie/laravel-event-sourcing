<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\EloquentStoredEventRepository;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot;

class EloquentStoredEventRepositoryTest extends TestCase
{
    /** @test */
    public function it_can_get_the_latest_version_id_for_a_given_aggregate_uuid()
    {
        $eloquentStoredEventRepository = new EloquentStoredEventRepository();

        $this->assertEquals(0, $eloquentStoredEventRepository->getLatestVersion('uuid-non-existing'));

        $aggregateRoot = AccountAggregateRoot::retrieve('uuid-1');
        $this->assertEquals(0, $eloquentStoredEventRepository->getLatestVersion('uuid-1'));

        $aggregateRoot->addMoney(100)->persist();
        $this->assertEquals(1, $eloquentStoredEventRepository->getLatestVersion('uuid-1'));

        $aggregateRoot->addMoney(100)->persist();
        $this->assertEquals(2, $eloquentStoredEventRepository->getLatestVersion('uuid-1'));

        $anotherAggregateRoot = AccountAggregateRoot::retrieve('uuid-2');
        $anotherAggregateRoot->addMoney(100)->persist();
        $this->assertEquals(1, $eloquentStoredEventRepository->getLatestVersion('uuid-2'));
        $this->assertEquals(2, $eloquentStoredEventRepository->getLatestVersion('uuid-1'));
    }
}
