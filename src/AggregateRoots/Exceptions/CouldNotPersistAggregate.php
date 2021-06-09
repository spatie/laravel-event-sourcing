<?php

namespace Spatie\EventSourcing\AggregateRoots\Exceptions;

use Exception;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class CouldNotPersistAggregate extends Exception
{
    public static function unexpectedVersionAlreadyPersisted(
        AggregateRoot $aggregateRoot,
        string $uuid,
        int $expectedVersion,
        int $actualVersion
    ) {
        $aggregateRootClass = class_basename($aggregateRoot);

        return new static("Could not persist aggregate {$aggregateRootClass} (uuid: {$uuid}) because it seems to be changed by another process after it was retrieved in the current process. Expect to persist events after version {$expectedVersion}, but version {$actualVersion} was already persisted.");
    }

    public static function invalidVersion(
        AggregateRoot $aggregateRoot,
        int $currentVersion
    ) {
        $aggregateRootClass = class_basename($aggregateRoot);

        $uuid = $aggregateRoot->uuid();

        return new static("Could not persist aggregate {$aggregateRootClass} (uuid: {$uuid}) because it seems to be changed by another process after it was retrieved in the current process. Current in-memory version is {$currentVersion}");
    }
}
