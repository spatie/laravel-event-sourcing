<?php

namespace Spatie\EventProjector\Console\Make;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeProjectorCommand extends GeneratorCommand
{
    protected $name = 'make:projector';

    protected $description = 'Create a new projector';

    protected $type = 'Projector';

    public function handle()
    {
        parent::handle();

        if (! $this->option('sync')) {
            return;
        }

        $this->rewriteToSyncProjector();
    }

    protected function getStub()
    {
        return __DIR__.'/../../../stubs/projector.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Projectors';
    }

    protected function rewriteToSyncProjector()
    {
        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->getPath($name);

        $content = file_get_contents($path);

        $content = str_replace('implements Projector', 'implements SyncProjector', $content);
        $content = str_replace('use Spatie\EventProjector\Projectors\Projector;', 'use Spatie\EventProjector\Projectors\SyncProjector;', $content);

        file_put_contents($path, $content);
    }

    protected function getOptions()
    {
        return [
            ['sync','s', InputOption::VALUE_NONE, 'Create a SyncProjector'],
        ];
    }
}
