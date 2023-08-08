<?php

namespace Spatie\EventSourcing\Tests\Console;

use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;

use Spatie\EventSourcing\EventRegistry;
use Spatie\EventSourcing\Tests\TestClasses\Events\TestEvent;

beforeEach(function () {
    $this->eventRegistry = app(EventRegistry::class);
});

it('can clear the registered storable events', function () {
    $this->eventRegistry->addEventClass(TestEvent::class);

    $this->artisan('event-sourcing:cache-storable-events')->assertExitCode(0);

    assertFileExists(config('event-sourcing.cache_path').'/storable-events.php');

    $this->artisan('event-sourcing:clear-storable-events')->assertExitCode(0);

    assertFileDoesNotExist(config('event-sourcing.cache_path').'/storable-events.php');
});
