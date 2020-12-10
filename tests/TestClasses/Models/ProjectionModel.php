<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Models;

use Illuminate\Database\Schema\Blueprint;
use Spatie\EventSourcing\Projections\Projection;

/**
 * @method static self create(array $properties)
 * @method static self find(int $id)
 */
class ProjectionModel extends Projection
{
    protected $guarded = [];

    public function getBlueprint(Blueprint $table): void
    {
        $table->uuid('uuid');
        $table->string('field')->nullable();
        $table->timestamps();
    }

    public function getMorphClass()
    {
        return 'projectionModel';
    }
}
