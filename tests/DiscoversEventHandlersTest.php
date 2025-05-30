<?php

namespace Spatie\EventSourcing\Tests;

use function PHPUnit\Framework\assertEqualsCanonicalizing;

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

function getDiscoveryBasePath(): string
{
    return realpath(test()->pathToTests().'/../');
}

it('can get all classes that have event handlers', function () {
    /** @var \Spatie\EventSourcing\Projectionist $projectionist */
    $projectionist = app(Projectionist::class);

    $pathToComposerJson = __DIR__.'/../composer.json';

    (new DiscoverEventHandlers())
        ->within([__DIR__.'/TestClasses/AutoDiscoverEventHandlers'])
        ->useBasePath(getDiscoveryBasePath())
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

it('filters out non-existent classes from cached event handlers', function () {
    // Create a mock EventSourcingServiceProvider to test the protected method
    $serviceProvider = new class(app()) extends \Spatie\EventSourcing\EventSourcingServiceProvider {
        public function testDiscoverEventHandlers()
        {
            return $this->discoverEventHandlers();
        }

        public function testGetCachedEventHandlers(): ?array
        {
            return $this->getCachedEventHandlers();
        }

        public function mockCachedEventHandlers(array $handlers): void
        {
            // Create a temporary cache file with mixed valid and invalid classes
            $cachePath = config('event-sourcing.cache_path', storage_path('framework/cache'));

            if (!is_dir($cachePath)) {
                mkdir($cachePath, 0755, true);
            }

            file_put_contents(
                $cachePath . '/event-handlers.php',
                '<?php return ' . var_export($handlers, true) . ';'
            );
        }

        public function cleanupCache(): void
        {
            $cachePath = config('event-sourcing.cache_path', storage_path('framework/cache'));
            $cacheFile = $cachePath . '/event-handlers.php';

            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        }
    };

    // Create a mix of valid and invalid event handler class names
    $cachedHandlers = [
        TestProjector::class, // This exists
        TestReactor::class,   // This exists
        'App\\NonExistentProjector', // This doesn't exist
        'App\\AnotherFakeHandler',   // This doesn't exist
    ];

    // Mock the cached event handlers
    $serviceProvider->mockCachedEventHandlers($cachedHandlers);

    // Get the initial projectionist state
    /** @var \Spatie\EventSourcing\Projectionist $projectionist */
    $projectionist = app(Projectionist::class);

    // Clear any existing handlers
    $projectionist->withoutEventHandlers();

    // Test that the discovery method filters out non-existent classes
    $serviceProvider->testDiscoverEventHandlers();

    // Get the registered handlers
    $registeredProjectors = $projectionist
        ->getProjectors()
        ->toBase()
        ->map(function (\Spatie\EventSourcing\EventHandlers\EventHandler $eventHandler) {
            return get_class($eventHandler);
        })
        ->values()
        ->toArray();

    $registeredReactors = $projectionist
        ->getReactors()
        ->toBase()
        ->map(function (\Spatie\EventSourcing\EventHandlers\EventHandler $eventHandler) {
            return get_class($eventHandler);
        })
        ->values()
        ->toArray();

    // Verify that only existing classes are registered
    expect($registeredProjectors)->toContain(TestProjector::class);
    expect($registeredReactors)->toContain(TestReactor::class);

    // Verify that non-existent classes are NOT registered
    expect($registeredProjectors)->not->toContain('App\\NonExistentProjector');
    expect($registeredReactors)->not->toContain('App\\AnotherFakeHandler');

    // Verify the total count matches only valid handlers
    expect($registeredProjectors)->toHaveCount(1);
    expect($registeredReactors)->toHaveCount(1);

    // Clean up
    $serviceProvider->cleanupCache();
});
