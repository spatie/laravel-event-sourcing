<?php

namespace Spatie\EventSourcing\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\EventSourcing\ShouldBeStored;
use Spatie\EventSourcing\Snapshot;
use Spatie\EventSourcing\StoredEvent;
use Spatie\SchemalessAttributes\SchemalessAttributes;

class EloquentSnapshot extends Model
{
    public $guarded = [];

    public $timestamps = false;

    protected $table = 'snapshots';

    public $casts = [
        'state' => 'array',
    ];

    public function toSnapshot(): Snapshot
    {
        return new Snapshot($this->aggregate_uuid, $this->aggregate_version, $this->state);
    }

    public function getStateAttribute(): SchemalessAttributes
    {
        return SchemalessAttributes::createForModel($this, 'state');
    }

    public function scopeUuid(Builder $query, string $uuid): void
    {
        $query->where('aggregate_uuid', $uuid);
    }
}
