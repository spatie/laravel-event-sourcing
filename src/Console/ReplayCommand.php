<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Spatie\EventProjector\Projectionist;
use Spatie\EventProjector\Console\Concerns\ReplaysEvents;
use Spatie\EventProjector\Console\Concerns\SelectsProjectors;

class ReplayCommand extends Command
{
    use ReplaysEvents, SelectsProjectors;

    protected $signature = 'event-projector:replay {projector?*}
                            {--force : Force the operation to run when in production}';

    protected $description = 'Replay stored events';

    /** @var \Spatie\EventProjector\Projectionist */
    protected $projectionist;

    /** @var string */
    protected $storedEventModelClass;

    public function __construct(Projectionist $projectionist)
    {
        parent::__construct();

        $this->projectionist = $projectionist;

        $this->storedEventModelClass = $this->getStoredEventClass();
    }

    public function handle()
    {
        $projectors = $this->selectsProjectors($this->argument('projector'), 'Are you sure you want to replay events to all projectors?');

        if (is_null($projectors)) {
            $this->warn('No events replayed!');

            return;
        }

        $this->replay($projectors);
    }
}
