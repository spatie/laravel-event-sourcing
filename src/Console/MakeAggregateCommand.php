<?php

namespace Spatie\EventSourcing\Console;

use Illuminate\Console\GeneratorCommand;

class MakeAggregateCommand extends GeneratorCommand
{
    protected $name = 'make:aggregate';

    protected $description = 'Create a new aggregate';

    protected $type = 'Aggregate';

    protected function getStub()
    {
        return __DIR__.'/../../stubs/aggregate.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Aggregates';
    }
}
