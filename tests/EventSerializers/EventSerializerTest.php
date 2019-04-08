<?php

namespace Spatie\EventProjector\Tests\EventSerializers;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\EventSerializers\EventSerializer;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventProjector\Tests\TestClasses\Events\EventWithoutSerializedModels;

final class EventSerializerTest extends TestCase
{
    /** @var \Spatie\EventProjector\EventSerializers\EventSerializer */
    protected $eventSerializer;

    public function setUp(): void
    {
        parent::setUp();

        $this->eventSerializer = app(EventSerializer::class);
    }

    /** @test */
    public function it_can_serialize_a_plain_event()
    {
        $event = new EventWithoutSerializedModels('test');

        $json = $this->eventSerializer->serialize($event);

        $array = json_decode($json, true);

        $this->assertEquals([
            'value' => 'test',
        ], $array);
    }

    /** @test */
    public function it_can_serialize_an_event_containing_a_model()
    {
        $account = Account::create(['name' => 'test']);
        $event = new MoneyAddedEvent($account, 1234);

        $json = $this->eventSerializer->serialize($event);
        $event = $this->eventSerializer->deserialize(get_class($event), $json);

        $this->assertEquals($account->id, $event->account->id);
        $this->assertEquals('test', $event->account->name);
        $this->assertEquals(1234, $event->amount);
    }

    /** @test */
    public function it_serializes_an_event_to_json()
    {
        $account = Account::create();

        $event = new MoneyAddedEvent($account, 1234);

        $json = $this->eventSerializer->serialize($event);

        $array = json_decode($json, true);

        $this->assertEquals([
            'account' => [
                'class' => get_class($account),
                'id' => 1,
                'relations' => [],
                'connection' => 'mysql',
            ],
            'amount' => 1234,
        ], $array);
    }
}
