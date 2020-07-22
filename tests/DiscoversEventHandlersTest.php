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

class DiscoversEventHandlersTest extends TestCase
{
    /** @test */
    public function it_can_get_all_classes_that_have_event_handlers()
    {
        /** @var \Spatie\EventSourcing\Projectionist $projectionist */
        $projectionist = app(Projectionist::class);

        $pathToComposerJson = __DIR__.'/../composer.json';

        (new DiscoverEventHandlers())
            ->within([__DIR__.'/TestClasses/AutoDiscoverEventHandlers'])
            ->useBasePath($this->getDiscoveryBasePath())
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

        $this->assertEqualsCanonicalizing([
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

        $this->assertEqualsCanonicalizing([
            TestReactorInSubdirectory::class,
            TestReactor::class,
        ], $registeredReactors);
    }

    private function getDiscoveryBasePath(): string
    {
        return realpath($this->pathToTests().'/../');
    }
}
