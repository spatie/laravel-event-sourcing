<?php

namespace Spatie\EventProjector\Console;

use Exception;
use Illuminate\Console\Command;
use Spatie\EventProjector\Console\Concerns\SelectsProjectors;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Console\Concerns\ReplaysEvents;

class RebuildCommand extends Command
{
    use ReplaysEvents, SelectsProjectors;

    protected $signature = 'event-projector:rebuild {projector?*}';

    protected $description = 'Rebuild a projector';

    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    public function __construct(EventProjectionist $eventProjectionist)
    {
        parent::__construct();

        $this->eventProjectionist = $eventProjectionist;
    }

    public function handle()
    {
        $projectors = $this->selectsProjectors($this->argument('projector'), 'Are you sure to rebuild all projectors?');

        if (is_null($projectors)) {
            $this->warn('No projectors rebuild!');

            return;
        }

        $projectors->each->reset();

        $this->replayEvents($projectors);

        $this->comment('Projector(s) rebuild!');
    }
}
