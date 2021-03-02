<?php

namespace Spatie\EventSourcing\Commands;

use ReflectionClass;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\Exceptions\CommandHandlerNotFound;
use Spatie\EventSourcing\Exceptions\MissingAggregateUuid;

class CommandBus
{
    public function dispatch(object $command): mixed
    {
        $handler = $this->resolveHandler($command);

        return $handler($command);
    }

    private function resolveHandler(object $command): callable
    {
        $attribute = (new ReflectionClass($command))->getAttributes(HandledBy::class)[0] ?? null;

        if (! $attribute) {
            throw new CommandHandlerNotFound($command::class);
        }

        $handlerClass = ($attribute->newInstance())->handlerClass;

        if (is_subclass_of($handlerClass, AggregateRoot::class)) {
            return $this->resolveHandlerForAggregateRoot($command, $handlerClass);
        }

        return app($handlerClass);
    }

    private function resolveHandlerForAggregateRoot(object $command, string $handlerClass): callable
    {
        $constructorParameters = (new ReflectionClass($command))->getConstructor()->getParameters();

        $uuidField = null;

        foreach ($constructorParameters as $constructorParameter) {
            $attribute = $constructorParameter->getAttributes(AggregateUuid::class);

            if (! count($attribute)) {
                continue;
            }

            $uuidField = $constructorParameter->getName();

            break;
        }

        if (! $uuidField) {
            throw new MissingAggregateUuid($command::class);
        }

        return new AggregateRootCommandHandler(
            $handlerClass,
            $command->{$uuidField}
        );
    }
}
