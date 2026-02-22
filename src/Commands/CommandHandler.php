<?php

namespace Spatie\EventSourcing\Commands;

use ReflectionClass;
use Spatie\Attributes\Attributes;
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
        $attribute = Attributes::get($this->command, HandledBy::class);

        if (! $attribute) {
            throw new CommandHandlerNotFound($this->command::class);
        }

        $handlerClass = $attribute->handlerClass;

        if (is_subclass_of($handlerClass, AggregateRoot::class)) {
            $this->resolveHandlerForAggregateRoot($handlerClass);

            return;
        }

        $this->handler = app($handlerClass);
    }

    private function resolveHandlerForAggregateRoot(string $handlerClass): void
    {
        $uuidField = null;

        foreach ((new ReflectionClass($this->command))->getProperties() as $property) {
            if (Attributes::onProperty($this->command, $property->getName(), AggregateUuid::class)) {
                $uuidField = $property->getName();

                break;
            }
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
