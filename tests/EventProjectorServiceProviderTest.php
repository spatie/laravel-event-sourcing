<?php

namespace Tests\Spatie\EventProjector;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Projectors\EmptyProjector;
use Spatie\EventProjector\Tests\TestClasses\Reactors\EmptyReactor;

class EventProjectorServiceProviderTest extends TestCase
{
    /** @var \Spatie\EventProjector\EventProjectionist */
    private $eventProjectionist;

    public function setUp()
    {
        parent::setUp();

        $this->eventProjectionist = app(EventProjectionist::class);
    }

    /** @test */
    public function projectors_can_be_registered_via_config()
    {
        $projectors = $this->eventProjectionist->getProjectors();

        $this->assertTrue($projectors->isNotEmpty());

        $this->assertEquals(EmptyProjector::class, $projectors->first());
    }

    /** @test */
    public function reactors_can_be_registered_via_config()
    {
        $reactors = $this->eventProjectionist->getReactors();

        $this->assertTrue($reactors->isNotEmpty());

        $this->assertEquals(EmptyReactor::class, $reactors->first());
    }

    protected function getPackageProviders($app)
    {
        $app['config']->set('event-projector.projectors', [
            EmptyProjector::class,
        ]);

        $app['config']->set('event-projector.reactors', [
            EmptyReactor::class,
        ]);

        return parent::getPackageProviders($app);
    }
}
