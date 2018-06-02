<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\ShouldBeCalledImmediately;

class ProjectorThatShouldBeCalledImmediately extends BalanceProjector implements ShouldBeCalledImmediately
{
}
