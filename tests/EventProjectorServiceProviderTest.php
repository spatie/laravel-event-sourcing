<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\EventSourcingServiceProvider;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;

final class EventProjectorServiceProviderTest extends TestCase
{
    /** @test */
    public function it_will_automatically_register_event_handlers_from_the_config_file()
    {
        config()->set('event-sourcing.projectors', [BalanceProjector::class]);
        config()->set('event-sourcing.reactors', [BrokeReactor::class]);

        (new EventSourcingServiceProvider($this->app))->register();

        $this->assertCount(1, Projectionist::getProjectors());
        $this->assertCount(1, Projectionist::getReactors());
    }
}
