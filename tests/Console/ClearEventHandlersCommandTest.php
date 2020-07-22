<?php

namespace Spatie\EventSourcing\Tests\Console;

use Spatie\EventSourcing\Projectionist;
use Spatie\EventSourcing\Tests\TestCase;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;

class ClearEventHandlersCommandTest extends TestCase
{
    private Projectionist $projectionist;

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

        $this->artisan('event-sourcing:cache-event-handlers')->assertExitCode(0);

        $this->assertFileExists(config('event-sourcing.cache_path').'/event-handlers.php');

        $this->artisan('event-sourcing:clear-event-handlers')->assertExitCode(0);

        $this->assertFileDoesNotExist(config('event-sourcing.cache_path').'/event-handlers.php');
    }
}
