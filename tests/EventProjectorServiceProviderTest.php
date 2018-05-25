<?php

namespace Tests\Spatie\EventProjector;

use ReflectionClass;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestCase;

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
        /** @var \Illuminate\Support\Collection $projectors */
        $projectors = $this->getProtectedProperty($this->eventProjectionist, 'projectors');

        $this->assertTrue($projectors->isNotEmpty());

        $this->assertInstanceOf(DummyProjector::class, $projectors->first());
    }


    /** @test */
    public function reactors_can_be_registered_via_config()
    {
        /** @var \Illuminate\Support\Collection $reactors */
        $reactors = $this->getProtectedProperty($this->eventProjectionist, 'reactors');

        $this->assertTrue($reactors->isNotEmpty());

        $this->assertInstanceOf(DummyReactor::class, $reactors->first());
    }

    protected function getProtectedProperty($class, $property)
    {
        $reflection = new ReflectionClass($class);

        $property = $reflection->getProperty($property);

        $property->setAccessible(true);

        $propertyValue = $property->getValue($class);

        return $propertyValue;
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
