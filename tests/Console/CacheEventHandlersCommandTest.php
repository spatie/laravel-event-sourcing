<?php

namespace Spatie\EventProjector\Tests\Console;

use Spatie\Snapshots\MatchesSnapshots;
use Spatie\EventProjector\Projectionist;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

final class CacheEventHandlersCommandTest extends TestCase
{
    use MatchesSnapshots;

    /** @var \Spatie\EventProjector\Projectionist */
    private $projectionist;

    public function setUp(): void
    {
        parent::setUp();

        $this->projectionist = app(Projectionist::class);
    }

    /** @test */
    public function it_can_cache_the_registered_projectors()
    {
        $this->projectionist->addProjector(BalanceProjector::class);

        $this->projectionist->addReactor(BrokeReactor::class);

        $this->artisan('event-projector:cache-event-handlers')->assertExitCode(0);

        $this->assertMatchesSnapshot(file_get_contents(config('event-projector.cache_path').'/event-handlers.php'));
    }
}
