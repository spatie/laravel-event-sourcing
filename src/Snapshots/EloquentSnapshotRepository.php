<?php

namespace Spatie\EventSourcing\Snapshots;

use Spatie\EventSourcing\AggregateRoots\Exceptions\InvalidEloquentSnapshotModel;

class EloquentSnapshotRepository implements SnapshotRepository
{
    protected string $snapshotModel;

    public function __construct()
    {
        $this->snapshotModel = (string)config('event-sourcing.snapshot_model', EloquentSnapshot::class);

        if (! new $this->snapshotModel() instanceof EloquentSnapshot) {
            throw new InvalidEloquentSnapshotModel("The class {$this->snapshotModel} must extend EloquentSnapshot");
        }
    }

    public function retrieve(string $aggregateUuid): ?Snapshot
    {
        /** @var \Illuminate\Database\Query\Builder $query */
        $query = $this->snapshotModel::query();

        if ($snapshot = $query->orderByDesc('id')->uuid($aggregateUuid)->first()) {
            return $snapshot->toSnapshot();
        }

        return null;
    }

    public function persist(Snapshot $snapshot): Snapshot
    {
        /** @var \Spatie\EventSourcing\Snapshots\EloquentSnapshot $eloquentSnapshot */
        $eloquentSnapshot = new $this->snapshotModel();

        $eloquentSnapshot->aggregate_uuid = $snapshot->aggregateUuid;
        $eloquentSnapshot->aggregate_version = $snapshot->aggregateVersion;
        $eloquentSnapshot->state = $snapshot->state;

        $eloquentSnapshot->save();

        return $eloquentSnapshot->toSnapshot();
    }
}
