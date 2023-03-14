<?php

namespace Spatie\EventSourcing\Projections;

use Illuminate\Database\Eloquent\Model;
use Spatie\EventSourcing\Projections\Concerns\Projectionable;
use Spatie\EventSourcing\Projections\Contracts\Writable;

/**
 * @method static static create(array $parameters = [])
 * @method static static|null find(string $uuid)
 */
abstract class Projection extends Model implements Writable
{
    use Projectionable;
}
