<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Models\ProjectorStatus;
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
        $this->listProjectorsWithMissingEvents();

        $this->listAllProjectors();
    }

    private function listProjectorsWithMissingEvents()
    {
        $this->title('Projectors that have not receveived all events yet');

        $header = ['Name', 'Last processed event id', 'Stream', 'Last event received at'];

        $rows = ProjectorStatus::query()
            ->where('has_received_all_events', false)
            ->get()
            ->map(function(ProjectorStatus $projectorStatus) {
                return [
                    $projectorStatus->getProjector()->getName(),
                    $projectorStatus->last_processed_event_id,
                    $projectorStatus->stream,
                    $projectorStatus->updated_at,
                ];
            })
            ->sortBy(function(array $projectorStatusRow) {
                return $projectorStatusRow[0];
            })
            ->toArray();

        $this->table($header, $rows);
    }

    protected function listAllProjectors(): void
    {
        $this->title('All projectors');

        $header = ['Name', 'Up to date', 'Last processed event id', 'Last event processed at'];

        $projectors = $this->eventProjectionist->getProjectors();

        if ($projectors->isEmpty()) {
            $this->warn('No projectors found. You can register projector like this : `Spatie\EventProjector\Facades\EventProjectionist::addProjector($projectorClassName)`.');

            return;
        }

        $rows = $projectors
            ->map(function (Projector $projector) {
                return [
                    $projector->getName(),
                    $projector->hasReceivedAllEvents() ? '✅' : '❌',
                    $projector->getLastProcessedEventId(),
                    $projector->lastEventProcessedAt()->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();

        $this->table($header, $rows);
    }

    protected function title(string $title)
    {
        $this->warn('');
        $this->warn($title);
        $this->warn(str_repeat('-', strlen($title)));
    }




}
