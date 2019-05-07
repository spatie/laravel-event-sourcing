<?php

namespace Spatie\EventProjector\Console;

use Exception;
use InvalidArgumentException;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Projectionist;
use Spatie\EventProjector\Models\StoredEvent;

class ReplayCommand extends Command
{
    protected $signature = 'event-projector:replay {projector?*}
                            {--from=0 : Replay events starting from this event number}
                            {--store= : Replay events from this store}';

    protected $description = 'Replay stored events';

    /** @var \Spatie\EventProjector\Projectionist */
    protected $projectionist;

    /** @var string */
    protected $storedEventModelClass;

    public function __construct(Projectionist $projectionist)
    {
        parent::__construct();

        $this->projectionist = $projectionist;
    }

    public function handle(): void
    {
        $this->storedEventModelClass = $this->getStoredEventClass();

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
            if (! $confirmed = $this->confirm('Are you sure you want to replay events to all projectors?')) {
                return null;
            }

            return $this->projectionist->getProjectors();
        }

        return collect($projectorClassNames)
            ->map(function (string $projectorName) {
                return ltrim($projectorName, '\\');
            })
            ->map(function (string $projectorName) {
                if (! $projector = $this->projectionist->getProjector($projectorName)) {
                    throw new Exception("Projector {$projectorName} not found. Did you register it?");
                }

                return $projector;
            });
    }

    public function replay(Collection $projectors, int $startingFrom): void
    {
        $replayCount = $this->getStoredEventClass()::startingFrom($startingFrom)->count();

        if ($replayCount === 0) {
            $this->warn('There are no events to replay');

            return;
        }

        $this->comment("Replaying {$replayCount} events...");

        $bar = $this->output->createProgressBar($this->getStoredEventClass()::count());
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

    private function getStoredEventClass(): string
    {
        if ($store = $this->option('store')) {
            if (! is_subclass_of($store, StoredEvent::class)) {
                throw new InvalidArgumentException(
                    "Invalid store value - class `$store` does not implement `".StoredEvent::class.'`'
                );
            }
        }

        return $store ?? config('event-projector.stored_event_model');
    }
}
