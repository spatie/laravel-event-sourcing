<?php

namespace Spatie\EventProjector\Tests\Models;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Models\EloquentStoredEvent;
use Spatie\EventProjector\Exceptions\InvalidStoredEvent;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

final class StoredEventTest extends TestCase
{
    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

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
        $this->setConfig('event-projector.event_class_map', [
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

        $this->setConfig('event-projector.event_class_map', [
            'money_added' => MoneyAddedEvent::class,
        ]);

        $this->fireEvents();

        $instance = EloquentStoredEvent::first();
        $payload = (new EloquentStoredEvent())->getConnection()->raw("CAST('{\"ip\": \"127.0.0.1\"}' AS JSON)");

        $this->assertArrayHasKey('ip', $instance->meta_data->toArray());
        $this->assertSame('127.0.0.1', $instance->meta_data['ip']);
        $this->assertDatabaseHas('stored_events', ['meta_data' => $payload]);

        EloquentStoredEvent::flushEventListeners();
    }

    public function fireEvents(int $number = 1, string $className = MoneyAddedEvent::class)
    {
        foreach (range(1, $number) as $i) {
            event(new $className($this->account, 1234));
        }
    }
}
