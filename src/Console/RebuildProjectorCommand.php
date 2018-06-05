<?php

namespace Spatie\EventProjector\Console;

use Exception;
use Illuminate\Console\Command;
use Spatie\EventProjector\Console\Concerns\ReplaysEvents;
use Spatie\EventProjector\EventProjectionist;

class RebuildProjectorCommand extends Command
{
    use ReplaysEvents;

    protected $signature = 'event-projector:rebuild-projector {projectorName*}';

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
        $projectorNames = $this->argument('projectorName');

        $projectors = collect($projectorNames)
            ->map(function (string $projectorName) {
                if (!$projector = $this->eventProjectionist->getProjector($projectorName)) {
                    throw new Exception("Projector {$projectorName} not found. Did you register?");
                }

                return $projector;
            })
            ->each->reset();

        $this->replayEvents($projectors);

        $this->comment('Projector(s) rebuild!');
    }
}
