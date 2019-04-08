<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\QueuedProjector as QueuedProjectorInterface;

final class QueuedProjector extends BalanceProjector implements QueuedProjectorInterface
{
}
