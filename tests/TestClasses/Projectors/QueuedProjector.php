<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\QueuedProjector as QueuedProjectorInterface;

class QueuedProjector extends BalanceProjector implements QueuedProjectorInterface
{
}
