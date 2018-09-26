<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Support\Facades\Artisan;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

class ListCommandTest extends TestCase
{
    /** @test */
    public function it_can_list_all_existing_projectors()
    {
        Projectionist::addProjector(BalanceProjector::class);

        Artisan::call('event-projector:list');

        $this->assertSeeInConsoleOutput(BalanceProjector::class);
    }

    /** @test */
    public function it_works_when_no_projectors_are_added_to_the_projectionist()
    {
        Artisan::call('event-projector:list');

        $this->assertSeeInConsoleOutput('No projectors found.');
    }
}
