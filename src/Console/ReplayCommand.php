<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Console\Concerns\ReplaysEvents;
use Spatie\EventProjector\Console\Concerns\SelectsProjectors;

class ReplayCommand extends Command
{
    use ReplaysEvents, SelectsProjectors;

    protected $signature = 'event-projector:replay {projector?*}';

    protected $description = 'Replay stored events';

    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    /** @var string */
    protected $storedEventModelClass;

    public function __construct(EventProjectionist $eventProjectionist, string $storedEventModelClass)
    {
        parent::__construct();

        $this->eventProjectionist = $eventProjectionist;

        $this->storedEventModelClass = $storedEventModelClass;
    }

    public function handle()
    {
        $projectors = $this->selectsProjectors($this->argument('projector'), 'Are you sure you want to replay events to all projectors?');

        if (is_null($projectors)) {
            $this->warn('No events replayed!');

            return;
        }

        $this->replayEvents($projectors);
    }
}
