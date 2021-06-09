---
title: Snapshots
weight: 4
---

Snapshots are a way to reduce the amount of events you need to fetch and apply when instantiating your aggregate. When you have a large number of events per aggregate root, it might be something to consider to improve performance.

## Creating a snapshot

You can create a new snapshot by calling the `->snapshot()` method on your Aggregate.

```php
$myAggregate = MyAggregate::retrieve($uuid);
$myAggregate->snapshot();
```

This will create a new snapshot in the `snapshots` table. By default we store the values of the public properties on the aggregate at that point in time.

When you retrieve the aggregate the next time, it will find the snapshot, set its internal properties back to what they were at the time of the snapshot, and apply any new events starting from the snapshot.   

This uses the snapshots `aggregateVersion`, this version number is incremented each time an event is applied by the aggregate and stored in the snapshot.

## Customizing the stored snapshot state

If you want to customize the state that the snapshot stores of your aggregate, you can override the `getState()` and `useState()` methods on the aggregate.

### getState
The default implementation uses the Reflection api to get all properties and values of the aggregate and stores them as a serialized array in the database.

The only requirement here is that you return an array to be stored.
```php
protected function getState(): array
{
    $class = new ReflectionClass($this);

    return collect($class->getProperties(ReflectionProperty::IS_PUBLIC))
        ->reject(fn (ReflectionProperty $reflectionProperty) => $reflectionProperty->isStatic())
        ->mapWithKeys(function (ReflectionProperty $property) {
            return [$property->getName() => $this->{$property->getName()}];
        })->toArray();
}
```

### useState
The default implementation gets every property from the array and sets them on the instance.
```php
protected function useState(array $state): void
{
    foreach ($state as $key => $value) {
        $this->$key = $value;
    }
}
```

## Want to know more?

Aggregate roots are a crucial part in large applications. Our course, [Event Sourcing in Laravel](https://event-sourcing-laravel.com/) covers them in depth:

- 09. Aggregate Roots
- 16. Event Versioning
- 17. Snapshotting
