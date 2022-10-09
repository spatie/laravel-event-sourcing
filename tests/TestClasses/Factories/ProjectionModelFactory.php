<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Factories;

use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\Support\ProjectionFactory;
use Spatie\EventSourcing\Tests\TestClasses\Models\ProjectionModel;

class ProjectionModelFactory extends ProjectionFactory
{
    protected $model = ProjectionModel::class;

	/**
	 * @inheritDoc
	 */
	public function definition()
	{
		return [
            'uuid' => Uuid::uuid4(),
        ];
	}
}
