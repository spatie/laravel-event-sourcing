<?php

namespace Spatie\EventProjector\Tests;

use Spatie\EventProjector\Composer;
use Spatie\EventProjector\Projectionist;
use Spatie\EventProjector\DiscoverEventHandlers;
use Spatie\EventProjector\EventHandlers\EventHandler;
use Spatie\EventProjector\Tests\TestClasses\AutoDiscoverEventHandlers\TestReactor;
use Spatie\EventProjector\Tests\TestClasses\AutoDiscoverEventHandlers\TestProjector;
use Spatie\EventProjector\Tests\TestClasses\AutoDiscoverEventHandlers\TestQueuedProjector;
use Spatie\EventProjector\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory\TestReactorInSubdirectory;
use Spatie\EventProjector\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory\TestProjectorInSubdirectory;
use Spatie\EventProjector\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory\TestQueuedProjectorInSubdirectory;

final class DiscoversEventHandlersTest extends TestCase
{
    /** @test */
    public function it_can_get_all_classes_that_have_event_handlers()
    {
        /** @var \Spatie\EventProjector\Projectionist $projectionist */
        $projectionist = app(Projectionist::class);

        $pathToComposerJson = __DIR__.'/../composer.json';

        (new DiscoverEventHandlers())
            ->within([__DIR__.'/TestClasses/AutoDiscoverEventHandlers'])
            ->useBasePath($this->getDiscoveryBasePath())
            ->useRootNamespace('Spatie\EventProjector\\')
            ->ignoringFiles(Composer::getAutoloadedFiles($pathToComposerJson))

            ->addToProjectionist($projectionist);

        $registeredProjectors = $projectionist
            ->getProjectors()
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
