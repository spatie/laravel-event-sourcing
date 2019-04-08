<?php

namespace Spatie\EventProjector\Tests;

use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\EventProjectorServiceProvider;
use Spatie\EventProjector\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

final class EventProjectorServiceProviderTest extends TestCase
{
    /** @test */
    public function it_will_automatically_register_event_handlers_from_the_config_file()
    {
        config()->set('event-projector.projectors', [BalanceProjector::class]);
        config()->set('event-projector.reactors', [BrokeReactor::class]);

        (new EventProjectorServiceProvider($this->app))->register();

        $this->assertCount(1, Projectionist::getProjectors());
        $this->assertCount(1, Projectionist::getReactors());
    }
}
