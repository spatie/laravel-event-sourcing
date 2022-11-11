<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\DummyAggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors\GetMetaDataProjector;
use function PHPUnit\Framework\assertEquals;

it('can store the aggregate root uuid on the event so the project can get it', function () {
    Projectionist::addProjector(GetMetaDataProjector::class);

    $aggregateRoot = DummyAggregateRoot::retrieve('my-uuid');

    $aggregateRoot->dummy();

    $aggregateRoot->persist();

    assertEquals('my-uuid', GetMetaDataProjector::$foundAggregateRootUuid);
});
