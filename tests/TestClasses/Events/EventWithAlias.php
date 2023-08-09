<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Spatie\EventSourcing\Attributes\EventAlias;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

#[EventAlias('event-with-alias-from-attribute')]
class EventWithAlias extends ShouldBeStored
{
}
