<?php

namespace Spatie\EventSourcing\Support;

use Illuminate\Database\Eloquent\Factories\Factory;

abstract class ProjectionFactory extends Factory
{
    public function newModel(array $attributes = [])
    {
        return parent::newModel($attributes)->writeable();
    }
}
