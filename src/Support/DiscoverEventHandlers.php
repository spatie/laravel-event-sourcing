<?php

namespace Spatie\EventSourcing\Support;

use Illuminate\Support\Collection;
use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\Projectionist;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class DiscoverEventHandlers extends DiscoveryHelper
{
    public function addToProjectionist(Projectionist $projectionist)
    {
        if (empty($this->directories)) {
            return;
        }

        $files = (new Finder())->files()->in($this->directories);

        return collect($files)
            ->reject(fn (SplFileInfo $file) => in_array($file->getPathname(), $this->ignoredFiles))
            ->map(fn (SplFileInfo $file) => $this->fullyQualifiedClassNameFromFile($file))
            ->filter(fn (string $eventHandlerClass) => is_subclass_of($eventHandlerClass, EventHandler::class))
            ->pipe(function (Collection $eventHandlers) use ($projectionist) {
                $projectionist->addEventHandlers($eventHandlers->toArray());
            });
    }
}
