<?php

namespace Spatie\EventSourcing\Tests\TestClasses;

class ProjectorWithWeightTestHelper
{
    public array $calledBy = [];

    public function calledBy(string $className): void
    {
        $this->calledBy[] = $className;
    }
}
