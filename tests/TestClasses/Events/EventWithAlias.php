<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Spatie\EventSourcing\Attributes\EventAlias;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

#[EventAlias('event_with_alias')]
class EventWithAlias extends ShouldBeStored
{
}
