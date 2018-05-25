<?php

namespace Tests\Spatie\EventProjector;

use ReflectionClass;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;

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

        $this->assertEquals(DummyProjector::class, $projectors->first());
    }

    /** @test */
    public function reactors_can_be_registered_via_config()
    {
        $reactors = $this->eventProjectionist->getReactors();

        $this->assertTrue($reactors->isNotEmpty());

        $this->assertEquals(DummyReactor::class, $reactors->first());
    }

    protected function getPackageProviders($app)
    {
        $app['config']->set('event-projector.projectors', [
            DummyProjector::class,
        ]);

        $app['config']->set('event-projector.reactors', [
            DummyReactor::class,
        ]);

        return parent::getPackageProviders($app);
    }
}

class DummyProjector implements Projector
{
    use ProjectsEvents;
}

class DummyReactor
{
}
