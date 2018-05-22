<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\GeneratorCommand;

class MakeStorableEventCommand extends GeneratorCommand
{
    protected $name = 'make:storable-event';

    protected $description = 'Create a storable event';

    protected $type = 'Storable event';

    protected function getStub()
    {
        return __DIR__.'/../../stubs/storable-event.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Events';
    }
}
