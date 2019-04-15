<?php

namespace Spatie\EventProjector;

use SplFileInfo;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Spatie\EventProjector\EventHandlers\EventHandler;

final class DiscoverEventHandlers
{
    private $directories = [];

    private $basePath;

    private $rootNamespace = '';

    public function __construct()
    {
        $this->basePath = app_path();
    }

    public function within(array $directories): self
    {
        $this->directories = $directories;

        return $this;
    }

    public function useBasePath(string $basePath): self
    {
        $this->basePath = $basePath;

        return $this;
    }

    public function useRootNamespace(string $rootNamespace): self
    {
        $this->rootNamespace = $rootNamespace;

        return $this;
    }

    public function addToProjectionist(Projectionist $projectionist)
    {
        $files = (new Finder())->files()->in($this->directories);

        return collect($files)
            ->map(function (SplFileInfo $file) {
                return static::fullQualifiedClassNameFromFile($file);
            })
            ->filter(function (string $eventHandlerClass) {
                return is_subclass_of($eventHandlerClass, EventHandler::class);
            })
            ->pipe(function (Collection $eventHandlers) use ($projectionist) {
                $projectionist->addEventHandlers($eventHandlers->toArray());
            });
    }

    protected function fullQualifiedClassNameFromFile(SplFileInfo $file): string
    {
        $class = trim(str_replace($this->basePath, '', $file->getRealPath()), DIRECTORY_SEPARATOR);

        $class = str_replace(
            [DIRECTORY_SEPARATOR, 'App\\'],
            ['\\', app()->getNamespace()],
            ucfirst(Str::replaceLast('.php', '', $class))
        );

        return $this->rootNamespace.$class;
    }
}
