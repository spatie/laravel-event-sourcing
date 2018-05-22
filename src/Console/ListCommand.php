<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Projectors\Projector;

class ListCommand extends Command
{
    protected $signature = 'event-projector:list';

    protected $description = 'List all event projectors';

    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    public function __construct(EventProjectionist $eventProjectionist)
    {
        parent::__construct();

        $this->eventProjectionist = $eventProjectionist;
    }

    public function handle()
    {
        $titles = ['name', 'up to date', 'last processed event id', 'last event processed at'];

        $rows = $this->eventProjectionist->projectors
            ->map(function (Projector $projector) {
                return [
                    $projector->getName(),
                    $projector->hasReceivedAllEvents() ? '✅' : '🛑',
                    $projector->getLastProcessedEventId(),
                    $projector->lastEventProcessedAt()->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();

        $this->table($titles, $rows);
    }
}
