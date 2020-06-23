<?php

namespace Spatie\EventSourcing\Tests\Models;

use Spatie\EventSourcing\Exceptions\InvalidStoredEvent;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\Models\EloquentStoredEvent;
use Spatie\EventSourcing\StoredEvent;
use Spatie\EventSourcing\Tests\TestCase;
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
            $event->meta_data['ip'] = '127.0.0.1';
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

        $this->assertEquals(MoneyAddedEvent::class, get_class($storedEvent->event));
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

        $this->assertEquals(MoneyAddedEvent::class, get_class($storedEvent->event));
    }

    public function fireEvents(int $number = 1, string $className = MoneyAddedEvent::class)
    {
        foreach (range(1, $number) as $i) {
            event(new $className($this->account, 1234));
        }
    }
}
