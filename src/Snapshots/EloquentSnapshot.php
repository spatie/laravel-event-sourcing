<?php

namespace Spatie\EventSourcing\Snapshots;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Builder;

class EloquentSnapshot extends Model
{
    public $guarded = [];

    protected $table = 'snapshots';

    public $casts = [
        'state' => 'array',
    ];

    public function toSnapshot(): Snapshot
    {
        return new Snapshot($this->aggregate_uuid, $this->aggregate_version, $this->state);
    }

    public function scopeUuid(Builder $query, string $uuid): void
    {
        $query->where('aggregate_uuid', $uuid);
    }
}
