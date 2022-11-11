<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\AttributeProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorThatWritesMetaData;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithAssociativeAndNonAssociativeHandleEvents;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithoutHandlesEvents;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectThatHandlesASingleEvent;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ResettableProjector;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

beforeEach(function () {
    AttributeProjector::$handledEvents = [];
});

it('can reach the stored event and write meta data to it', function () {
    Projectionist::addProjector(ProjectorThatWritesMetaData::class);

    event(new MoneyAddedEvent(Account::create(), 1234));

    assertCount(1, EloquentStoredEvent::get());

    assertEquals(1, EloquentStoredEvent::first()->meta_data['user_id']);
});

it('can be reset', function () {
    Account::create();

    $projector = new ResettableProjector();

    Projectionist::addProjector($projector);

    assertCount(1, Account::all());

    $projector->reset();

    assertCount(0, Account::all());
});

it('can handle non associative handle events', function () {
    $account = Account::create();

    $projector = new ProjectorWithAssociativeAndNonAssociativeHandleEvents();

    Projectionist::addProjector($projector);

    event(new MoneyAddedEvent($account, 1234));

    assertEquals(1234, $account->refresh()->amount);
});

it('can handle mixed handle events', function () {
    $account = Account::create();

    $projector = new ProjectorWithAssociativeAndNonAssociativeHandleEvents();

    Projectionist::addProjector($projector);

    event(new MoneyAddedEvent($account, 1234));

    event(new MoneySubtractedEvent($account, 4321));

    assertEquals(-3087, $account->refresh()->amount);
});

it('can handle a single event', function () {
    $account = Account::create();

    $projector = new ProjectThatHandlesASingleEvent();

    Projectionist::addProjector($projector);

    event(new MoneyAddedEvent($account, 1234));

    assertEquals(1234, $account->refresh()->amount);
});

it('can find the right method for the right event without the need to specify handles events', function () {
    $account = Account::create();

    Projectionist::addProjector(ProjectorWithoutHandlesEvents::class);

    event(new MoneyAddedEvent($account, 1234));
    assertCount(1, EloquentStoredEvent::get());
    assertEquals(1234, $account->refresh()->amount);

    event(new MoneySubtractedEvent($account, 34));
    assertCount(2, EloquentStoredEvent::get());
    assertEquals(1200, $account->refresh()->amount);
});
