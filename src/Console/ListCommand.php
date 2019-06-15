<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Projectionist;
use Spatie\EventProjector\EventHandlers\EventHandler;

final class ListCommand extends Command
{
    protected $signature = 'event-projector:list';

    protected $description = 'Lists all event handlers';

    public function handle(Projectionist $projectionist)
    {
        $this->info('');
        $projectors = $projectionist->getProjectors();
        $rows = $this->convertEventHandlersToTableRows($projectors);
        count($rows)
            ? $this->table(['Event', 'Handled by projectors'], $rows)
            : $this->warn('No projectors registered');

        $this->info('');
        $projectors = $projectionist->getReactors();
        $rows = $this->convertEventHandlersToTableRows($projectors);
        count($rows)
            ? $this->table(['Event', 'Handled by reactors'], $rows)
            : $this->warn('No reactors registered');
    }

    private function convertEventHandlersToTableRows(Collection $eventHandlers): array
    {
        $events = $eventHandlers
            ->reduce(function ($events, EventHandler $eventHandler) {
                $eventHandler->getEventHandlingMethods()->each(function (string $method, string $eventClass) use (&$events, $eventHandler) {
                    $events[$eventClass][] = get_class($eventHandler);
                });

                return $events;
            }, []);

        return collect($events)->map(function (array $eventHandlers, string $eventClass) {
            return [$eventClass, implode(PHP_EOL, collect($eventHandlers)->sort()->toArray())];
        })
            ->sort()
            ->values()
            ->toArray();
    }
}
