<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Spatie\EventProjector\Projectionist;
use Spatie\EventProjector\Console\Concerns\SelectsProjectors;

class ResetCommand extends Command
{
    use SelectsProjectors;

    protected $signature = 'event-projector:reset {projector*}';

    protected $description = 'Reset a projector';

    /** @var \Spatie\EventProjector\Projectionist */
    protected $projectionist;

    public function __construct(Projectionist $projectionist)
    {
        parent::__construct();

        $this->projectionist = $projectionist;
    }

    public function handle()
    {
        $projectors = $this->selectsProjectors($this->argument('projector'), 'Are you sure to reset all projectors?');

        if (is_null($projectors)) {
            $this->warn('No projectors reset!');

            return;
        }

        $projectors->each->reset();

        $this->comment('Projector(s) reset!');
    }
}
