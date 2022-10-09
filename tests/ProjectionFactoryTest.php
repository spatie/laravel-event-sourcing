<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Tests\TestClasses\Factories\ProjectionModelFactory;

class ProjectionFactoryTest extends \Orchestra\Testbench\TestCase
{
    public function test_a_projection_factory_can_bypass_the_readonly_restriction()
    {
        $factory = new ProjectionModelFactory();

        $model = $factory->create();

        $this->assertTrue($model->exists);
    }
}
