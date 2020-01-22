<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Projectors\QueuedProjector as QueuedProjectorInterface;

class QueuedProjector extends BalanceProjector implements QueuedProjectorInterface
{
}
