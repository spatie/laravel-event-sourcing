<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Str;
use Spatie\EventProjector\EventHandlers\EventHandler;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\QueuedProjector;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

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
            ->each(function (string $fqcn) use ($projectionist) {
                if (is_subclass_of($fqcn, Projector::class)) {
                    $projectionist->addProjector($fqcn);

                    return;
                };

                if (is_subclass_of($fqcn, QueuedProjector::class)) {
                    $projectionist->addProjector($fqcn);

                    return;
                };

                if (is_subclass_of($fqcn, EventHandler::class)) {
                    $projectionist->addReactor($fqcn);

                    return;
                };
            });
    }

    protected function fullQualifiedClassNameFromFile(SplFileInfo $file): string {
        $class = trim(str_replace($this->basePath, '', $file->getRealPath()), DIRECTORY_SEPARATOR);

        $class = str_replace(
            [DIRECTORY_SEPARATOR, 'App\\'],
            ['\\', app()->getNamespace()],
            ucfirst(Str::replaceLast('.php', '', $class))
        );

        return $this->rootNamespace . $class;
    }
}

