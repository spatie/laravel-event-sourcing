<?php

namespace Spatie\EventProjector\Console;

use Exception;
use Illuminate\Console\Command;
use Spatie\EventProjector\Console\Concerns\SelectsProjectors;
use Spatie\EventProjector\EventProjectionist;

class ResetCommand extends Command
{
    use SelectsProjectors;

    protected $signature = 'event-projector:reset {projector*}';

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
        $projectors = $this->selectsProjectors($this->argument('projector'), 'Are you sure to reset all projectors?');

        if (is_null($projectors)) {
            $this->warn('No projectors reset!');

            return;
        }

        $projectors->each->reset();

        $this->comment('Projector(s) reset!');
    }
}
