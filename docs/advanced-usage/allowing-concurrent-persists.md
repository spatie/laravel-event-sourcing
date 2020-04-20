---
title: Allowing concurrent persists
weight: 11
---

Whenever an aggregate is persisted, its versions will increment for each event that was saved. When reconstituting an aggregate from previous events the aggregate will remember the highest version id for that aggregate. If that highest version id differs from the highest one that the aggregate remembers, an exception will be thrown. This can happen when there are two concurrent requests that try to update the same aggregate.

If you want to disable this check, and thus allow concurrent persists, you can set the `$allowConcurrency` static property on the aggregate.

```php
use Spatie\EventSourcing\AggregateRoot;

class AggregateRootThatAllowsConcurrency extends AggregateRoot
{
    protected static bool $allowConcurrency = true;
}
```
