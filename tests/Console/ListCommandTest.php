<?php

namespace Spatie\EventSourcing\Tests\Console;

use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors\AccountProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\MoneyAddedCountProjector;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;

it('can list all registered projectors and reactors', function () {
    Projectionist::addProjector(BalanceProjector::class);
    Projectionist::addProjector(AccountProjector::class);
    Projectionist::addProjector(MoneyAddedCountProjector::class);

    Projectionist::addReactor(BrokeReactor::class);

    $this->artisan('event-sourcing:list')->assertExitCode(0);
});
