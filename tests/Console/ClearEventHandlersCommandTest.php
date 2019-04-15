<?php

namespace Spatie\EventProjector\Tests\Console;

use Spatie\EventProjector\Projectionist;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

final class ClearEventHandlersCommandTest extends TestCase
{
    /** @var \Spatie\EventProjector\Projectionist */
    private $projectionist;

    public function setUp(): void
    {
        parent::setUp();

        $this->projectionist = app(Projectionist::class);
    }

    /** @test */
    public function it_can_clear_the_registered_projectors()
    {
        $this->projectionist->addProjector(BalanceProjector::class);

        $this->projectionist->addReactor(BrokeReactor::class);

        $this->artisan('event-projector:cache-event-handlers')->assertExitCode(0);

        $this->assertFileExists(config('event-projector.cache_path'));

        $this->artisan('event-projector:clear-event-handlers')->assertExitCode(0);

        $this->assertFileNotExists(config('event-projector.cache_path'));
    }
}
