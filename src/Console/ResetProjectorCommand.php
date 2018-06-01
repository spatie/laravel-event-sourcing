<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Console\Snapshots\Concerns\ChooseSnapshot;

class ResetProjectorCommand extends Command
{
    use ChooseSnapshot;

    protected $signature = 'event-projector:reset-projector {projectorName}';

    protected $description = 'Reset a projector';

    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    public function __construct(EventProjectionist $eventProjectionist)
    {
        parent::__construct();

        $this->eventProjectionist = $eventProjectionist;
    }

    public function handle()
    {
        $projectorName = $this->argument('projectorName');

        $projector = $this->eventProjectionist->getProjector($projectorName);

        if (! $projector) {
            $this->warn("No projector named `{$projectorName}` found!");

            return;
        }

        $projector->reset();

        $this->comment('Projector reset!');
    }
}
