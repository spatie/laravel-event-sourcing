<?php

namespace Spatie\EventProjector\Tests\Console;

use Illuminate\Support\Facades\Artisan;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\Projectors\AccountProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\MoneyAddedCountProjector;
use Spatie\EventProjector\Tests\TestClasses\Reactors\BrokeReactor;

final class ListCommandTest extends TestCase
{
    /** @test */
    public function it_can_list_all_registered_projectors_and_reactors()
    {
        Projectionist::addProjector(BalanceProjector::class);
        Projectionist::addProjector(AccountProjector::class);
        Projectionist::addProjector(MoneyAddedCountProjector::class);

        Projectionist::addReactor(BrokeReactor::class);

       $this->artisan('event-projector:list')->assertExitCode(0);
    }
}

