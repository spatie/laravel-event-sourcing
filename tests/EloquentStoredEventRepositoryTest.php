<?php

namespace Spatie\EventSourcing\Tests;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Spatie\EventSourcing\StoredEvents\Repositories\EloquentStoredEventRepository;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot;

class EloquentStoredEventRepositoryTest extends TestCase
{
    /** @test */
    public function it_can_get_the_latest_version_id_for_a_given_aggregate_uuid()
    {
        $eloquentStoredEventRepository = new EloquentStoredEventRepository();

        $this->assertEquals(0, $eloquentStoredEventRepository->getLatestAggregateVersion('uuid-non-existing'));

        $aggregateRoot = AccountAggregateRoot::retrieve('uuid-1');
        $this->assertEquals(0, $eloquentStoredEventRepository->getLatestAggregateVersion('uuid-1'));

        $aggregateRoot->addMoney(100)->persist();
        $this->assertEquals(1, $eloquentStoredEventRepository->getLatestAggregateVersion('uuid-1'));

        $aggregateRoot->addMoney(100)->persist();
        $this->assertEquals(2, $eloquentStoredEventRepository->getLatestAggregateVersion('uuid-1'));

        $anotherAggregateRoot = AccountAggregateRoot::retrieve('uuid-2');
        $anotherAggregateRoot->addMoney(100)->persist();
        $this->assertEquals(1, $eloquentStoredEventRepository->getLatestAggregateVersion('uuid-2'));
        $this->assertEquals(2, $eloquentStoredEventRepository->getLatestAggregateVersion('uuid-1'));
    }
	
	/** @test */
	public function it_can_retreive_events_in_a_custom_order()
	{
		$this->setConfig('event-sourcing.stored_event_order_by', 'created_at');
		
		$eloquentStoredEventRepository = new EloquentStoredEventRepository();
		$uuid = Str::uuid();
		$realNow = Date::now();
		
		/** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
		$aggregateRoot = AccountAggregateRoot::retrieve($uuid);
		
		Date::setTestNow($realNow->copy()->addMinutes(10));
		$aggregateRoot->multiplyMoney(2)->persist();
		
		Date::setTestNow($realNow);
		$aggregateRoot->addMoney(100)->persist();
		
		$aggregateRoot = AccountAggregateRoot::retrieve($uuid);
		
		$this->assertEquals(200, $aggregateRoot->balance);
		
		$eventIds = $eloquentStoredEventRepository->retrieveAll($uuid)->pluck('id')->toArray();
		
		$this->assertEquals(2, $eventIds[0]);
		$this->assertEquals(1, $eventIds[1]);
	}
}
