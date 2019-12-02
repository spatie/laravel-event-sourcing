<?php

namespace Spatie\EventSourcing\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\EventSourcing\Projectionist;
use Spatie\EventSourcing\StoredEventRepository;

class ReplayCommand extends Command
{
    protected $signature = 'event-sourcing:replay {projector?*}
                            {--from=0 : Replay events starting from this event number}
                            {--stored-event-model= : Replay events from this store}';

    protected $description = 'Replay stored events';

    protected Projectionist $projectionist;

    public function __construct(Projectionist $projectionist)
    {
        parent::__construct();

        $this->projectionist = $projectionist;
    }

    public function handle(): void
    {
        $projectors = $this->selectProjectors($this->argument('projector'));

        if (is_null($projectors)) {
            $this->warn('No events replayed!');

            return;
        }

        $this->replay($projectors, $this->option('from'));
    }

    public function selectProjectors(array $projectorClassNames): ?Collection
    {
        if (count($projectorClassNames ?? []) === 0) {
            if (! $confirmed = $this->confirm('Are you sure you want to replay events to all projectors?', true)) {
                return null;
            }

            return $this->projectionist->getProjectors();
        }

        return collect($projectorClassNames)
            ->map(fn(string $projectorName) => ltrim($projectorName, '\\'))
            ->map(function (string $projectorName) {
                if (! $projector = $this->projectionist->getProjector($projectorName)) {
                    throw new Exception("Projector {$projectorName} not found. Did you register it?");
                }

                return $projector;
            });
    }

    public function replay(Collection $projectors, int $startingFrom): void
    {
        $repository = app(StoredEventRepository::class);
        $events = $repository->retrieveAllStartingFrom($startingFrom);
        $replayCount = $events->count();

        if ($replayCount === 0) {
            $this->warn('There are no events to replay');

            return;
        }

        $this->comment("Replaying {$replayCount} events...");

        $bar = $this->output->createProgressBar($events->count());
        $onEventReplayed = function () use ($bar) {
            $bar->advance();
        };

        $this->projectionist->replay($projectors, $startingFrom, $onEventReplayed);

        $bar->finish();

        $this->emptyLine(2);
        $this->comment('All done!');
    }

    private function emptyLine(int $amount = 1): void
    {
        foreach (range(1, $amount) as $i) {
            $this->line('');
        }
    }
}
