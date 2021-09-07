<?php

namespace Spatie\EventSourcing\Enums;

class MetaData
{
    public const AGGREGATE_ROOT_UUID = 'aggregate-root-uuid';
    public const STORED_EVENT_ID = 'stored-event-id';
    public const CREATED_AT = 'created-at';
    public const AGGREGATE_ROOT_VERSION = 'aggregate-root-version';
}
