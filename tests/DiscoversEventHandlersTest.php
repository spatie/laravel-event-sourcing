<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\Projectionist;
use Spatie\EventSourcing\Support\Composer;
use Spatie\EventSourcing\Support\DiscoverEventHandlers;
use Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory\TestProjectorInSubdirectory;
use Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory\TestQueuedProjectorInSubdirectory;
use Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory\TestReactorInSubdirectory;
use Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers\TestProjector;
use Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers\TestQueuedProjector;
use Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers\TestReactor;
use function PHPUnit\Framework\assertEqualsCanonicalizing;

beforeEach(function () {
    $this->getDiscoveryBasePath = function (): string
    {
        return realpath($this->pathToTests().'/../');
    };
});

test('it can get all classes that have event handlers', function () {
    /** @var \Spatie\EventSourcing\Projectionist $projectionist */
    $projectionist = app(Projectionist::class);

    $pathToComposerJson = __DIR__.'/../composer.json';

    $getDiscoveryBasePath = $this->getDiscoveryBasePath;

    (new DiscoverEventHandlers())
        ->within([__DIR__.'/TestClasses/AutoDiscoverEventHandlers'])
        ->useBasePath($getDiscoveryBasePath())
        ->useRootNamespace('Spatie\EventSourcing\\')
        ->ignoringFiles(Composer::getAutoloadedFiles($pathToComposerJson))

        ->addToProjectionist($projectionist);

    $registeredProjectors = $projectionist
        ->getProjectors()
        ->toBase()
        ->map(function (EventHandler $eventHandler) {
            return get_class($eventHandler);
        })
        ->values()
        ->toArray();

    assertEqualsCanonicalizing([
        TestQueuedProjector::class,
        TestProjectorInSubdirectory::class,
        TestQueuedProjectorInSubdirectory::class,
        TestProjector::class,
    ], $registeredProjectors);

    $registeredReactors = $projectionist
        ->getReactors()
        ->toBase()
        ->map(function (EventHandler $eventHandler) {
            return get_class($eventHandler);
        })

        ->values()
        ->toArray();

    assertEqualsCanonicalizing([
        TestReactorInSubdirectory::class,
        TestReactor::class,
    ], $registeredReactors);
});
