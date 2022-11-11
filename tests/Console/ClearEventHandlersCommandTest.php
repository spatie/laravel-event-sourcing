<?php

namespace Spatie\EventSourcing\Tests\Console;

use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;

use Spatie\EventSourcing\Projectionist;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;

beforeEach(function () {
    $this->projectionist = app(Projectionist::class);
});

it('can clear the registered projectors', function () {
    $this->projectionist->addProjector(BalanceProjector::class);

    $this->projectionist->addReactor(BrokeReactor::class);

    $this->artisan('event-sourcing:cache-event-handlers')->assertExitCode(0);

    assertFileExists(config('event-sourcing.cache_path').'/event-handlers.php');

    $this->artisan('event-sourcing:clear-event-handlers')->assertExitCode(0);

    assertFileDoesNotExist(config('event-sourcing.cache_path').'/event-handlers.php');
});
