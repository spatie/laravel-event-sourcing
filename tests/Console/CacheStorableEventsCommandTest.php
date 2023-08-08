<?php

namespace Spatie\EventSourcing\Tests\Console;

use Spatie\EventSourcing\EventRegistry;
use Spatie\EventSourcing\Tests\TestClasses\Events\TestEvent;

use function Spatie\Snapshots\assertMatchesSnapshot;

beforeEach(function () {
    $this->eventRegistry = app(EventRegistry::class);
});

it('can cache the registered storable events', function () {
    $this->eventRegistry->addEventClass(TestEvent::class);

    $this->artisan('event-sourcing:cache-storable-events')->assertExitCode(0);

    assertMatchesSnapshot(file_get_contents(config('event-sourcing.cache_path').'/storable-events.php'));
});
