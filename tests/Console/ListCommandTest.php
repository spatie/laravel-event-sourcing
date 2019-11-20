<?php

namespace Spatie\EventSourcing\Tests\Console;

use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\Tests\TestCase;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors\AccountProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\MoneyAddedCountProjector;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;

final class ListCommandTest extends TestCase
{
    /** @test */
    public function it_can_list_all_registered_projectors_and_reactors()
    {
        Projectionist::addProjector(BalanceProjector::class);
        Projectionist::addProjector(AccountProjector::class);
        Projectionist::addProjector(MoneyAddedCountProjector::class);

        Projectionist::addReactor(BrokeReactor::class);

        $this->artisan('event-sourcing:list')->assertExitCode(0);
    }
}
