<?php

namespace Spatie\EventProjector\Console\Make;

use Illuminate\Console\GeneratorCommand;

class MakeReactorCommand extends GeneratorCommand
{
    protected $name = 'make:reactor';

    protected $description = 'Create a new reactor';

    protected $type = 'Reactor';

    protected function getStub()
    {
        return __DIR__.'/../../stubs/reactor.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Reactors';
    }
}
