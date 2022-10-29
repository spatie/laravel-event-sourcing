<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\EventSourcingServiceProvider;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;
use function PHPUnit\Framework\assertCount;

test('it will automatically register event handlers from the config file', function () {
    config()->set('event-sourcing.projectors', [BalanceProjector::class]);
    config()->set('event-sourcing.reactors', [BrokeReactor::class]);

    (new EventSourcingServiceProvider($this->app))->register();

    assertCount(1, Projectionist::getProjectors());
    assertCount(1, Projectionist::getReactors());
});
