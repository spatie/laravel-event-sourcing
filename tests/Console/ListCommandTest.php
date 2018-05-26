<?php

namespace Spatie\EventProjector\Console;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

class ListCommandTest extends TestCase
{
    /** @test */
    public function it_can_list_all_existing_projectors()
    {
        EventProjectionist::addProjector(BalanceProjector::class);

        $this->artisan('event-projector:list-projectors');

        $this
            ->assertSeeInConsoleOutput(BalanceProjector::class);
    }

    /** @test */
    public function it_works_when_no_projectors_are_added_to_the_projectionist()
    {
        $this->artisan('event-projector:list-projectors');

        $this->assertSeeInConsoleOutput('No projectors found.');
    }
}
