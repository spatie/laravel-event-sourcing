<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Spatie\EventProjector\Projectionist;
use Spatie\EventProjector\Console\Concerns\ReplaysEvents;
use Spatie\EventProjector\Console\Concerns\SelectsProjectors;

class RebuildCommand extends Command
{
    use ReplaysEvents, SelectsProjectors;

    protected $signature = 'event-projector:rebuild {projector?*}';

    protected $description = 'Rebuild a projector';

    /** @var \Spatie\EventProjector\Projectionist */
    protected $projectionist;

    public function __construct(Projectionist $projectionist)
    {
        parent::__construct();

        $this->projectionist = $projectionist;
    }

    public function handle()
    {
        $projectors = $this->selectsProjectors($this->argument('projector'), 'Are you sure to rebuild all projectors?');

        if (is_null($projectors)) {
            $this->warn('No projectors rebuild!');

            return;
        }

        $projectors->each->reset();

        $this->replay($projectors);

        $this->comment('Projector(s) rebuild!');
    }
}
