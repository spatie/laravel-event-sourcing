<?php

namespace Spatie\EventSourcing\Tests;

use function PHPUnit\Framework\assertEqualsCanonicalizing;

use Spatie\EventSourcing\EventRegistry;
use Spatie\EventSourcing\Support\Composer;
use Spatie\EventSourcing\Support\DiscoverStorableEvents;
use Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverStorableEvents\Subdirectory\TestStorableEventInSubdirectory;
use Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverStorableEvents\TestStorableEvent;

it('can get all storable event classes', function () {
    /** @var \Spatie\EventSourcing\EventRegistry $eventRegistry */
    $eventRegistry = app(EventRegistry::class);

    $pathToComposerJson = __DIR__.'/../composer.json';

    (new DiscoverStorableEvents())
        ->within([__DIR__.'/TestClasses/AutoDiscoverStorableEvents'])
        ->useBasePath(realpath(test()->pathToTests().'/../'))
        ->useRootNamespace('Spatie\EventSourcing\\')
        ->ignoringFiles(Composer::getAutoloadedFiles($pathToComposerJson))

        ->addToEventRegistry($eventRegistry);

    $registeredStorableEvents = $eventRegistry
        ->getClassMap()
        ->values()
        ->toArray();

    assertEqualsCanonicalizing([
        TestStorableEvent::class,
        TestStorableEventInSubdirectory::class,
    ], $registeredStorableEvents);
});
