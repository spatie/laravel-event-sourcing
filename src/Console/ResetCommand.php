<?php

namespace Spatie\EventProjector\Console;

use Exception;
use Illuminate\Console\Command;
use Spatie\EventProjector\EventProjectionist;

class ResetCommand extends Command
{
    protected $signature = 'event-projector:reset {projectorName*}';

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
        $projectorNames = $this->argument('projectorName');

        collect($projectorNames)
            ->map(function (string $projectorName) {
                if (! $projector = $this->eventProjectionist->getProjector($projectorName)) {
                    throw new Exception("Projector {$projectorName} not found. Did you register?");
                }

                return $projector;
            })
            ->each->reset();

        $this->comment('Projector(s) reset!');
    }
}
