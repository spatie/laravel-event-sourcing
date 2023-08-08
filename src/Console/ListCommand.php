<?php

namespace Spatie\EventSourcing\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\Facades\EventRegistry;
use Spatie\EventSourcing\Projectionist;

class ListCommand extends Command
{
    protected $signature = 'event-sourcing:list';

    protected $description = 'Lists all event handlers';

    public function handle(Projectionist $projectionist): void
    {
        $this->info('');
        $projectors = $projectionist->getProjectors();
        $rows = $this->convertEventHandlersToTableRows($projectors);
        count($rows)
            ? $this->table(['Event', 'Alias', 'Handled by projectors'], $rows)
            : $this->warn('No projectors registered');

        $this->info('');
        $reactors = $projectionist->getReactors();
        $rows = $this->convertEventHandlersToTableRows($reactors);
        count($rows)
            ? $this->table(['Event', 'Alias', 'Handled by reactors'], $rows)
            : $this->warn('No reactors registered');
    }

    protected function convertEventHandlersToTableRows(Collection $eventHandlers): array
    {
        $events = $eventHandlers
            ->reduce(function ($events, EventHandler $eventHandler) {
                $eventHandler
                    ->getEventHandlingMethods()
                    ->each(function (array $methods, string $eventClass) use (&$events, $eventHandler) {
                        $events[$eventClass][] = get_class($eventHandler);
                    });

                return $events;
            }, []);

        return collect($events)
            ->map(fn (array $eventHandlers, string $eventClass) => [
                $eventClass,
                EventRegistry::getAlias($eventClass),
                implode(PHP_EOL, collect($eventHandlers)->sort()->toArray()),
            ])
            ->sort()
            ->values()
            ->toArray();
    }
}
