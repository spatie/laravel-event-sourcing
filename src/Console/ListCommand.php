<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Spatie\EventProjector\Projectionist;
use Spatie\EventProjector\Projectors\Projector;

final class ListCommand extends Command
{
    protected $signature = 'event-projector:list';

    protected $description = 'Lists all event handlers';

    public function handle(Projectionist $projectionist)
    {
        $projectors = $projectionist->getProjectors();

        $projectors->each(function(Projector $projector) {
            $projector->getEventHandlingMethods();
        });
    }
}

