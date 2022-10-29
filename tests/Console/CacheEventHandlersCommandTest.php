<?php

namespace Spatie\EventSourcing\Tests\Console;

use Spatie\EventSourcing\Projectionist;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;
use function Spatie\Snapshots\assertMatchesSnapshot;

beforeEach(function () {
    $this->projectionist = app(Projectionist::class);
});

test('it can cache the registered projectors', function () {
    $this->projectionist->addProjector(BalanceProjector::class);

    $this->projectionist->addReactor(BrokeReactor::class);

    $this->artisan('event-sourcing:cache-event-handlers')->assertExitCode(0);

    assertMatchesSnapshot(file_get_contents(config('event-sourcing.cache_path').'/event-handlers.php'));
});
