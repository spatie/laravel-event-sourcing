<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\GeneratorCommand;

final class MakeStorableEventCommand extends GeneratorCommand
{
    protected $name = 'make:domain-event';

    protected $description = 'Create a storable event';

    protected $type = 'Domain event';

    protected function getStub()
    {
        return __DIR__.'/../../../stubs/event.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\StorableEvents';
    }
}
