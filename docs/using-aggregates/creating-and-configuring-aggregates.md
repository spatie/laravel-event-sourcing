---
title: Creating and configuring aggregates
weight: 2
---

An aggregate is a class that decides to record events based on past events.

## Creating an aggregate

The easiest way to create an aggregate root would be to use the `make:aggregate` command:

```php
php artisan make:aggregate MyAggregate
```

This will create a class like this:

```php
namespace App\Aggregates;

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;


class MyAggregate extends AggregateRoot
{
}
```

## Retrieving an aggregate

An aggregate can be retrieved like this:

```php
MyAggregate::retrieve($uuid)
```
If you don't want to use the static retrieve method, you can also retrieve an aggregate on an initialized AggregateRoot like this: 

```php
$myAggregate = new MyAggregate();
$myAggregate->loadUuid($uuid);
```

The `load` method is handy when injecting aggregate roots in constructors or classes where method injection is supported.

```php
public function handle(MyAggregate $aggregate) {
    $aggregate->load($uuid);
    
    // ...
}
```

This will cause all events with the given `uuid` to be retrieved and fed to the aggregate. For example, an event `MoneyAdded` will be passed to the `applyMoneyAdded` method on the aggregate if such a method exists.


## Recording events

Inside an aggregate you can record new events using the `recordThat` function. All events being passed to that function should implement `Spatie\EventSourcing\ShouldBeStored`.

Here's an example event

```php
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class MoneyAdded extends ShouldBeStored
{
    /** @var int */
    private $amount

    public function __construct(int $amount)
    {
        $this->amount = $amount;
    }
}
```

Inside an aggregate root you can pass the event to `recordThat`:

```php
// somewhere inside your aggregate
public function addMoney(int $amount)
{
    $this->recordThat(new MoneyAdded($amount));
}
```

Calling `recordThat` will not persist the event to the DB, that will happen when the aggregate itself gets persisted. However, recording an event will cause it getting applied to the aggregate immediately. For example, when you record the event `MoneyAdded`, we'll immediately call `applyMoneyAdded` on the aggregate.

Notice that your event isn't required to contain the `$uuid`. Your aggregate is built up for a specific `$uuid` and under the hood, the package will save that `$uuid` along with the event when the aggregate gets persisted.

## Persisting aggregates

To persist an aggregate call `persist` on it. Here's an example:

```php
MyAggregate::retrieve($uuid) // will cause all events for this uuid to be fed to the `apply*` methods
   // call methods that record events
   ->persist(); //
```

Persisting an aggregate root will write all newly recorded events to the database. The newly persisted events will get passed to all projectors and reactors.

By default, the event won't be fired on Laravel's event bus. To dispatch events when they are stored, you can set the `dispatch_events_from_aggregate_roots` value in the `event-sourcing` config file to `true`. 

## Want to know more?

Aggregate roots are a crucial part in large applications. Our course, [Event Sourcing in Laravel](https://event-sourcing-laravel.com/) covers them in depth:

- 09. Aggregate Roots
- 10. State Management in Aggregate Roots
- 11. Aggregate Partials
- 12. State Machines with Aggregate Partials
