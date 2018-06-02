<?php

namespace Spatie\EventProjector\Console\Make;

use Illuminate\Console\GeneratorCommand;

class MakeProjectorCommand extends GeneratorCommand
{
    protected $name = 'make:projector';

    protected $description = 'Create a new projector';

    protected $type = 'Projector';

    protected function getStub()
    {
        return __DIR__.'/../../../stubs/projector.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Projectors';
    }
}
