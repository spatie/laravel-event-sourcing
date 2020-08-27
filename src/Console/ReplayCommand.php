<?php

namespace Spatie\EventSourcing\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Spatie\EventSourcing\Projectionist;
use Spatie\EventSourcing\StoredEvents\Repositories\EloquentStoredEventRepository;
use Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository;

class ReplayCommand extends Command
{
    protected $signature = 'event-sourcing:replay {projector?*}
                            {--from=0 : Replay events starting from this event number}
                            {--stored-event-repository= : Replay events from a specific repository}';

    protected $description = 'Replay stored events';

    protected ?Projectionist $projectionist;

    protected ?StoredEventRepository $storedEventRepository;

    public function handle(Projectionist $projectionist): void
    {
        $this->storedEventRepository = app($this->getStoredEventRepositoryClass());

        $this->projectionist = $projectionist;

        $projectors = $this->selectProjectors($this->argument('projector'));

        if (is_null($projectors)) {
            $this->warn('No events replayed!');

            return;
        }

        $this->replay($projectors, (int)$this->option('from'));
    }

    public function selectProjectors(array $projectorClassNames): ?Collection
    {
        if (count($projectorClassNames) === 0) {
            if (! $this->confirm('Are you sure you want to replay events to all projectors?', true)) {
                return null;
            }

            return $this->projectionist->getProjectors();
        }

        return collect($projectorClassNames)
            ->map(fn (string $projectorName) => ltrim($projectorName, '\\'))
            ->map(function (string $projectorName) {
                if (! $projector = $this->projectionist->getProjector($projectorName)) {
                    throw new Exception("Projector {$projectorName} not found. Did you register it?");
                }

                return $projector;
            });
    }

    public function replay(Collection $projectors, int $startingFrom): void
    {
        $replayCount = $this->storedEventRepository->countAllStartingFrom($startingFrom);

        if ($replayCount === 0) {
            $this->warn('There are no events to replay');

            return;
        }

        $this->comment("Replaying {$replayCount} events...");

        $bar = $this->output->createProgressBar($replayCount);
        $onEventReplayed = function () use ($bar) {
            $bar->advance();
        };

        $this->projectionist->replay($projectors, $startingFrom, $onEventReplayed, $this->getStoredEventRepositoryClass());

        $bar->finish();

        $this->emptyLine(2);
        $this->comment('All done!');
    }

    /** @psalm-suppress UnusedVariable */
    private function emptyLine(int $amount = 1): void
    {
        foreach (range(1, $amount) as $i) {
            $this->line('');
        }
    }

    private function getStoredEventRepositoryClass(): string
    {
        $storedEventRepository = $this->option('stored-event-repository');

        if (! $storedEventRepository) {
            return EloquentStoredEventRepository::class;
        }

        if (! is_subclass_of($storedEventRepository, StoredEventRepository::class)) {
            throw new InvalidArgumentException(
                "Stored event repository class `$storedEventRepository` does not implement `" . StoredEventRepository::class . '`'
            );
        }

        return $storedEventRepository;
    }
}
