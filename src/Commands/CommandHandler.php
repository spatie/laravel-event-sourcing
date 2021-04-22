<?php

namespace Spatie\EventSourcing\Commands;

use ReflectionClass;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\AggregateRoots\Exceptions\MissingAggregateUuid;
use Spatie\EventSourcing\Commands\Exceptions\CommandHandlerNotFound;

class CommandHandler
{
    private object $command;

    private mixed $handler;

    private ?string $aggregateUuid = null;

    public static function for(object $command): self
    {
        return new self($command);
    }

    public function forAggregateRoot(): bool
    {
        return $this->aggregateUuid !== null;
    }

    public function aggregateUuid(): ?string
    {
        return $this->aggregateUuid;
    }

    public function lockId(): string
    {
        if (! $this->forAggregateRoot()) {
            return 'command-lock';
        }

        return "command-lock-{$this->aggregateUuid()}";
    }

    public function handle(): mixed
    {
        return ($this->handler)($this->command);
    }

    private function __construct(object $command)
    {
        $this->command = $command;

        $this->resolveHandler();
    }

    private function resolveHandler(): void
    {
        $attribute = (new ReflectionClass($this->command))->getAttributes(HandledBy::class)[0] ?? null;

        if (! $attribute) {
            throw new CommandHandlerNotFound($this->command::class);
        }

        $handlerClass = ($attribute->newInstance())->handlerClass;

        if (is_subclass_of($handlerClass, AggregateRoot::class)) {
            $this->resolveHandlerForAggregateRoot($handlerClass);

            return;
        }

        $this->handler = app($handlerClass);
    }

    private function resolveHandlerForAggregateRoot(string $handlerClass): void
    {
        $constructorParameters = (new ReflectionClass($this->command))->getConstructor()->getParameters();

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
            throw new MissingAggregateUuid($this->command::class);
        }

        $this->aggregateUuid = $this->command->{$uuidField};

        $this->handler = new AggregateRootCommandHandler(
            $handlerClass,
            $this->aggregateUuid
        );
    }
}
