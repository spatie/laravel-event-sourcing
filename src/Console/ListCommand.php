<?php

namespace Spatie\EventProjector\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Projectionist;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Models\ProjectorStatus;

class ListCommand extends Command
{
    protected $signature = 'event-projector:list';

    protected $description = 'List all event projectors';

    /** @var \Spatie\EventProjector\Projectionist */
    protected $projectionist;

    public function __construct(Projectionist $projectionist)
    {
        parent::__construct();

        $this->projectionist = $projectionist;
    }

    public function handle()
    {
        $projectors = $this->projectionist->getProjectors();

        if ($projectors->isEmpty()) {
            $this->warn('No projectors found. You can register projector like this : `Spatie\EventProjector\Facades\Projectionist::addProjector($projectorClassName)`.');

            return;
        }

        $this->listProjectorsWithMissingEvents();

        $this->list($projectors);
    }

    private function listProjectorsWithMissingEvents()
    {
        $header = ['Name', 'Last processed event id', 'Stream', 'Last event received at'];

        $rows = ProjectorStatus::query()
            ->where('has_received_all_events', false)
            ->get()
            ->map(function (ProjectorStatus $projectorStatus) {
                return [
                    $projectorStatus->getProjector()->getName(),
                    $projectorStatus->last_processed_event_id,
                    $projectorStatus->stream,
                    $projectorStatus->updated_at,
                ];
            })
            ->sortBy(function (array $projectorStatusRow) {
                return $projectorStatusRow[0];
            })
            ->toArray();

        if (count($rows)) {
            $this->title('Projectors that have not receveived all events yet');
            $this->table($header, $rows);
        }
    }

    protected function list(Collection $projectors): void
    {
        $this->title('All projectors');

        $header = ['Name', 'Last processed event id', 'Last event processed at'];

        $rows = $projectors
            ->map(function (Projector $projector) {
                return [
                    $projector->getName(),
                    $this->getLastProcessedEventId($projector),
                    optional($this->getLastEventProcessedAt($projector))->format('Y-m-d H:i:s') ?? '/',
                ];
            })
            ->toArray();

        $this->table($header, $rows);
    }

    public function getLastProcessedEventId(Projector $projector): int
    {
        return ProjectorStatus::query()
                ->where('projector_name', $projector->getName())
                ->max('last_processed_event_id') ?? 0;
    }

    public function getLastEventProcessedAt(Projector $projector): ?Carbon
    {
        $status = ProjectorStatus::query()
            ->where('projector_name', $projector->getName())
            ->orderBy('updated_at', 'desc')
            ->first();

        return optional($status)->updated_at;
    }

    protected function title(string $title)
    {
        $this->warn('');
        $this->warn($title);
        $this->warn(str_repeat('-', strlen($title)));
    }
}
