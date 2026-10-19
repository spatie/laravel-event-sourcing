<?php

namespace Spatie\EventSourcing\Tests\EventSerializers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeImmutable;

use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertSame;

use Spatie\EventSourcing\EventSerializers\EventSerializer;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithArray;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithCarbon;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithDatetime;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithDocblock;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithoutSerializedModels;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithShouldBeStoredPropertyNames;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\EventSerializer\DummySerializer;
use Spatie\EventSourcing\Tests\TestClasses\EventSerializer\UpgradeSerializer;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;

beforeEach(function () {
    $this->eventSerializer = app(EventSerializer::class);
});

it('reuses the default serializer for every event', function () {
    assertInstanceOf(config('event-sourcing.event_serializer'), $this->eventSerializer);
    assertSame($this->eventSerializer, app(EventSerializer::class));
});

it('keeps custom serializers transient', function () {
    $this->setConfig('event-sourcing.event_serializer', DummySerializer::class);

    $serializer = app(EventSerializer::class);

    assertInstanceOf(DummySerializer::class, $serializer);
    assertNotSame($serializer, app(EventSerializer::class));
    assertEquals('{"message":"message set by custom serializer"}', $serializer->serialize(new EventWithoutSerializedModels('test')));
});

it('does not leak event data when reusing the default serializer', function () {
    foreach (['first event', 'different second event', ''] as $value) {
        $event = new EventWithoutSerializedModels($value);
        $serializer = app(EventSerializer::class);
        $json = $serializer->serialize($event);

        assertSame(['value' => $value], json_decode($json, true));
        assertEquals($event, app(EventSerializer::class)->deserialize($event::class, $json, 1));
    }
});

it('can serialize a plain event', function () {
    $event = new EventWithoutSerializedModels('test');

    $json = $this->eventSerializer->serialize($event);

    $array = json_decode($json, true);

    assertEquals([
        'value' => 'test',
    ], $array);
});

it('does not serialize metaData into event properties', function () {
    $event = new EventWithoutSerializedModels('test');
    $event->setMetaData(['aggregate-root-uuid' => 'some-uuid']);

    $json = $this->eventSerializer->serialize($event);

    $array = json_decode($json, true);

    assertEquals(['value' => 'test'], $array);
    assertArrayNotHasKey('metaData', $array);
});

it('can serialize and deserialize an event with properties named like ShouldBeStored methods', function () {
    $event = new EventWithShouldBeStoredPropertyNames(
        id: 'lease-123',
        createdAt: '2024-01-15',
        aggregateRootUuid: 'custom-uuid',
    );

    $json = $this->eventSerializer->serialize($event);

    $array = json_decode($json, true);

    assertEquals('lease-123', $array['id']);
    assertEquals('2024-01-15', $array['createdAt']);
    assertEquals('custom-uuid', $array['aggregateRootUuid']);

    $deserialized = $this->eventSerializer->deserialize(get_class($event), $json, 1);

    assertEquals('lease-123', $deserialized->id);
    assertEquals('2024-01-15', $deserialized->createdAt);
    assertEquals('custom-uuid', $deserialized->aggregateRootUuid);
});

it('can serialize an event containing a model', function () {
    $account = Account::create(['name' => 'test']);
    $event = new MoneyAddedEvent($account, 1234);

    $json = $this->eventSerializer->serialize($event);
    $event = $this->eventSerializer->deserialize(get_class($event), $json, 1);

    assertEquals($account->id, $event->account->id);
    assertEquals('test', $event->account->name);
    assertEquals(1234, $event->amount);
});

it('serializes an event to json', function () {
    $account = Account::create();

    $event = new MoneyAddedEvent($account, 1234);

    $json = $this->eventSerializer->serialize($event);

    $array = json_decode($json, true);

    assertEquals(get_class($account), $array['account']['class'] ?? null);
    assertEquals(1, $array['account']['id'] ?? null);
    assertEquals(1234, $array['amount'] ?? null);
});

it('can deserialize an event with datetime', function () {
    $event = new EventWithDatetime(new DateTimeImmutable('now'));

    $json = $this->eventSerializer->serialize($event);

    /**
     * @var EventWithDatetime
     */
    $normalizedEvent = $this->eventSerializer->deserialize(get_class($event), $json, 1);

    assertInstanceOf(DateTimeImmutable::class, $normalizedEvent->value);
});

it('can deserialize an event with carbon', function () {
    $event = new EventWithCarbon(Carbon::now());

    $json = $this->eventSerializer->serialize($event);

    $normalizedEvent = $this->eventSerializer->deserialize(get_class($event), $json, 1);

    assertInstanceOf(CarbonInterface::class, $normalizedEvent->value);
});

it('can deserialize an event with an array', function () {
    $event = new EventWithArray([Carbon::now(), Carbon::now()]);

    $json = $this->eventSerializer->serialize($event);

    $normalizedEvent = $this->eventSerializer->deserialize(get_class($event), $json, 1);

    assertInstanceOf(CarbonInterface::class, $normalizedEvent->values[0]);
});

it('can deserialize an event with a docblock', function () {
    $event = new EventWithDocblock(Carbon::now());

    $json = $this->eventSerializer->serialize($event);

    $normalizedEvent = $this->eventSerializer->deserialize(get_class($event), $json, 1);

    assertInstanceOf(CarbonInterface::class, $normalizedEvent->value);
});

it('can upgrade an event version', function () {
    $event = new EventWithDatetime(new DateTimeImmutable('2019-08-07T00:00:00Z'));
    $eventSerializer = app(UpgradeSerializer::class);

    $json = $eventSerializer->serialize($event);

    /**
     * @var EventWithDatetime
     */
    $normalizedEvent = $eventSerializer->deserialize(get_class($event), $json, 1, '{ "version": 1 }');

    assertInstanceOf(DateTimeImmutable::class, $normalizedEvent->value);
    assertEquals('UTC', $normalizedEvent->value->getTimezone()->getName());
});
