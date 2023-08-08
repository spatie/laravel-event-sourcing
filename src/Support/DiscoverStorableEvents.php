<?php

namespace Spatie\EventSourcing\Support;

use Illuminate\Support\Collection;
use Spatie\EventSourcing\EventRegistry;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class DiscoverStorableEvents extends DiscoveryHelper
{
    public function addToEventRegistry(EventRegistry $eventRegistry)
    {
        if (empty($this->directories)) {
            return;
        }

        $files = (new Finder())->files()->in($this->directories);

        return collect($files)
            ->reject(fn (SplFileInfo $file) => in_array($file->getPathname(), $this->ignoredFiles))
            ->map(fn (SplFileInfo $file) => $this->fullyQualifiedClassNameFromFile($file))
            ->filter(fn (string $eventClass) => is_subclass_of($eventClass, ShouldBeStored::class))
            ->pipe(function (Collection $eventClasses) use ($eventRegistry) {
                $eventRegistry->addEventClasses($eventClasses->toArray());
            });
    }
}
