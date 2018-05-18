<?php

namespace Spatie\EventProjector\Tests;

use Spatie\EventProjector\EventSerializer;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;

class EventSerializerTest extends TestCase
{
    /** @var \Spatie\EventProjector\EventSerializer */
    protected $eventSerializer;

    public function setUp()
    {
        parent::setUp();

        $this->eventSerializer = new EventSerializer();
    }

    /** @test */
    public function it_can_serialize_an_event_containing_a_model()
    {
        $account = Account::create(['name' => 'test']);
        $event = new MoneyAdded($account, 1234);

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

        $event = new MoneyAdded($account, 1234);

        $json = $this->eventSerializer->serialize($event);

        $array = json_decode($json, true);

        $this->assertEquals([
            'account' => [
                'class' => get_class($account),
                'id' => 1,
                'relations' => [],
                'connection' => 'sqlite',
            ],
            'amount' => 1234,
        ], $array);
    }
}
