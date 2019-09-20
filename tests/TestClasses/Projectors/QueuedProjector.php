<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Projectors\QueuedProjector as QueuedProjectorInterface;

final class QueuedProjector extends BalanceProjector implements QueuedProjectorInterface
{
}
