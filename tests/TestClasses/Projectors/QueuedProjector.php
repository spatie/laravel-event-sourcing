<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Illuminate\Contracts\Queue\ShouldQueue;

class QueuedProjector extends BalanceProjector implements ShouldQueue
{
}
