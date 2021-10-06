<?php

namespace Spatie\EventSourcing\Tests\Models;

use Carbon\Carbon;
use Spatie\EventSourcing\Enums\MetaData;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\StoredEvents\Exceptions\InvalidStoredEvent;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\StoredEvents\StoredEvent;
use Spatie\EventSourcing\Tests\TestCase;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;

class StoredEventTest extends TestCase
{
    protected Account $account;

    public function setUp(): void
    {
        parent::setUp();

        Projectionist::addProjector(new BalanceProjector());

        $this->account = Account::create();
    }

    /** @test */
    public function it_has_a_scope_to_get_all_events_starting_from_given_id()
    {
        $this->fireEvents(4);

        $this->assertEquals([3, 4], EloquentStoredEvent::startingFrom(3)->pluck('id')->toArray());
    }

    /** @test */
    public function it_will_throw_a_human_readable_exception_when_the_event_couldnt_be_deserialized()
    {
        $this->fireEvents();

        // sneakily change the stored event class
        EloquentStoredEvent::first()->update(['event_class' => 'NonExistingClass']);

        $this->expectException(InvalidStoredEvent::class);

        EloquentStoredEvent::first()->toStoredEvent();
    }

    /** @test * */
    public function it_will_store_the_alias_when_a_classname_is_found_in_the_event_class_map()
    {
        $this->setConfig('event-sourcing.event_class_map', [
            'money_added' => MoneyAddedEvent::class,
        ]);

        $this->fireEvents();

        $this->assertEquals(MoneyAddedEvent::class, EloquentStoredEvent::first()->toStoredEvent()->event_class);
        $this->assertDatabaseHas('stored_events', ['event_class' => 'money_added']);
    }

    /** @test * */
    public function it_allows_to_modify_metadata_with_offset_set_in_eloquent_model()
    {
        EloquentStoredEvent::creating(function (EloquentStoredEvent $event) {
            $event->meta_data->set('ip', '127.0.0.1');
        });

        $this->setConfig('event-sourcing.event_class_map', [
            'money_added' => MoneyAddedEvent::class,
        ]);

        $this->fireEvents();

        $instance = EloquentStoredEvent::withMetaDataAttributes('ip', '127.0.0.1')->first();

        $this->assertInstanceOf(EloquentStoredEvent::class, $instance);
        $this->assertArrayHasKey('ip', $instance->meta_data->toArray());
        $this->assertSame('127.0.0.1', $instance->meta_data['ip']);

        EloquentStoredEvent::flushEventListeners();
    }

    /** @test * */
    public function it_can_handle_an_encoded_string_as_event_properties()
    {
        $this->fireEvents();

        $eloquentEvent = EloquentStoredEvent::first();

        $storedEvent = new StoredEvent([
            'id' => $eloquentEvent->id,
            'event_properties' => json_encode($eloquentEvent->event_properties),
            'aggregate_uuid' => $eloquentEvent->aggregate_uuid ?? '',
            'aggregate_version' => $eloquentEvent->aggregate_version ?? 0,
            'event_class' => $eloquentEvent->event_class,
            'meta_data' => $eloquentEvent->meta_data,
            'created_at' => $eloquentEvent->created_at,
        ]);

        $this->assertInstanceOf(MoneyAddedEvent::class, $storedEvent->event);
    }

    /** @test * */
    public function it_encodes_the_event_properties_itself_when_its_an_array()
    {
        $this->fireEvents();

        $eloquentEvent = EloquentStoredEvent::first();

        $this->assertIsArray($eloquentEvent->event_properties);

        $storedEvent = new StoredEvent([
            'id' => $eloquentEvent->id,
            'event_properties' => $eloquentEvent->event_properties,
            'aggregate_uuid' => $eloquentEvent->aggregate_uuid ?? '',
            'aggregate_version' => $eloquentEvent->aggregate_version ?? 0,
            'event_class' => $eloquentEvent->event_class,
            'meta_data' => $eloquentEvent->meta_data,
            'created_at' => $eloquentEvent->created_at,
        ]);

        $this->assertInstanceOf(MoneyAddedEvent::class, $storedEvent->event);
    }

    /** @test **/
    public function it_exposes_the_aggregate_version()
    {
        $this->fireEvents();

        $eloquentEvent = EloquentStoredEvent::first();

        $storedEvent = $eloquentEvent->toStoredEvent();

        $this->assertSame('0', $storedEvent->aggregate_version);
    }

    /** @test */
    public function it_uses_the_original_event_if_set()
    {
        $originalEvent = new MoneyAdded(100);

        $eloquentStoredEvent = new EloquentStoredEvent();

        $eloquentStoredEvent->setOriginalEvent($originalEvent);

        $this->assertSame($originalEvent, $eloquentStoredEvent->event);
    }

    /** @test */
    public function it_gets_created_at_from_stored_event_meta_data()
    {
        $originalEvent = new MoneyAddedEvent($this->account, 1234);
        $storedEvent = new StoredEvent([
            'event_properties' => null,
            'aggregate_uuid' => '',
            'aggregate_version' => '',
            'event_version' => null,
            'event_class' => MoneyAddedEvent::class,
            'meta_data' => [
                MetaData::CREATED_AT => '2021-10-05 12:00:00',
            ],
            'created_at' => '',

        ], $originalEvent);
        
        $this->assertEquals(Carbon::parse('2021-10-05 12:00:00'), $storedEvent->event->createdAt(), );
    }

    /** @test */
    public function created_at_is_set_on_event()
    {
        $now = Carbon::make('2021-01-01 10:00:00');

        Carbon::setTestNow($now);

        $this->fireEvents();

        /** @var \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent $eloquentEvent */
        $eloquentEvent = EloquentStoredEvent::first();

        $event = $eloquentEvent->toStoredEvent()->event;

        $this->assertTrue($event->createdAt()->eq($now));
    }

    /** @test */
    public function the_stored_event_id_is_set()
    {
        $this->fireEvents();

        /** @var \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent $eloquentEvent */
        $eloquentEvent = EloquentStoredEvent::first();

        $event = $eloquentEvent->toStoredEvent()->event;

        $this->assertEquals($eloquentEvent->id, $event->storedEventId());
    }

    public function fireEvents(int $number = 1, string $className = MoneyAddedEvent::class)
    {
        foreach (range(1, $number) as $i) {
            event(new $className($this->account, 1234));
        }
    }
}
