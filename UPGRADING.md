# Upgrading

## From v6 to v7

There were no changes to the public API, we only performed internal cleanup and provided support for Laravel 9

## From v5 to v6

The `EventHandler` interface was changed:

```diff
-    public function handles(): array;
+    public function handles(StoredEvent $storedEvent): bool;

-    public function handle(StoredEvent $event);
+    public function handle(StoredEvent $storedEvent): void;
```

## From v4 to v5

- Stored events now have an `event_version` column and property. You must add this migration manually.
- Make sure any aggregate roots with constructors call the parent `__construct` method
- Event listeners in aggregate roots, projectors and reactors all rely on type hints rather than method naming. Make sure all your methods use the appropriate type hints (the method name no longer matters). `$handlesEvents` on Projectors and Reactors isn't supported anymore.
- Dependency injection in handlers isn't supported anymore,  use constructor injection instead
- `$storedEvent` and `$aggregateRootUuid` are no longer passed to event handler methods. Use `$event->storedEventId()` and `$event->aggregateRootUuid()` instead. ([#180](https://github.com/spatie/laravel-event-sourcing/discussions/180))
- The `EloquentStoredEvent::query()->uuid()` is now `EloquentStoredEvent::query()->whereAggregateRoot()`
- `AggregateRoot::$allowConcurrency` is no longer supported
- Event handlers are no longer called with `app()->call()` ([#180](https://github.com/spatie/laravel-event-sourcing/discussions/180))
- See [5.0.0 release notes](https://github.com/spatie/laravel-event-sourcing/releases/tag/5.0.0) for all other changes

## From v3 to v4

- Projectors should not implement the `Projector` interface anymore. Instead, they should extend from `Spatie\EventSourcing\EventHandlers\Projectors\Projector` class. You don't need the use the `ProjectsEvents` trait anymore, as it's already applied on the base class.
- The `QueuedProjector` interface does not exist anymore. If you want to queue a projector you should let it implement the marker interface `lluminate\Contracts\Queue\ShouldQueue`.
- Reactors should not implement `EventHandler` anymore. Instead, they should extend from `Spatie\EventSourcing\EventHandlers\Reactors\Reactor`. In previous versions all reactors where called asynchronously via a job. If you want to keep that behavior (in most cases you'll want this), you should also let the reactor implement `lluminate\Contracts\Queue\ShouldQueue`.
- All classes that revolve around the concept of stored events have been moved to the `Spatie\EventSourcing\StoredEvents` namespace. If you are using any of those classes, take a look inside the source code of the package what the new namespace is.
- Aggregate roots are now in a dedicated separate namespace `Spatie\EventSourcing\AggregateRoots`. You should update all aggregate roots and fake aggregate roots to this namespace.
- The `ShouldBeStored` interface is now an abstract base class. In all your events you should extend it now, instead of implementing it
- the `reset` method has been removed on projectors, use `resetState` instead
- the `fake` method on an aggregate root now accepts a uuid instead of an array of events. Use `given` to pass the events you are now passing to `fake`
- the variable used to accept the event in the apply methods on aggregates is required to be named `$event`

## From v2 to v3

- Add an `aggregate_version` property to the `stored_events` table `$table->unsignedInteger('aggregate_version')->nullable();`
- Republish the migrations or copy the `create_snapshots_table` migration
- The `StoredEventRepository` interface has new methods called `retrieveAllAfterVersion` and `getLatestVersion` that you must implement if you have a custom repository
- The `StoredEventRepository` now accepts an `aggregateVersion` parameter in the `persist` and `persistMany` methods
- The `StoredEventRepository` has a new `countAllStartingFrom` method

## From v1 to v2

Nothing changed, expect that where possible property types and short closures are used

## From laravel-event-projector v3 to v1 of laravel-event-sourcing

The only change in this version is the naming change from `laravel-event-projector` to `laravel-event-sourcing`. There are no changes to the API.

To upgrade from v3 of `laravel-event-projector` you have to perform these steps:
1. Merge the `config/event-projector.php` with `vendor/spatie/laravel-event-sourcing/config/event-sourcing.php` 
2. Rename `config/event-projector.php` to `config/event-sourcing.php`
3. Change `laravel-event-projector:v3` to `laravel-event-sourcing:v1` and run `composer update`
4. The namespace has changed, so you need to replace `Spatie\EventProjector` by `Spatie\EventSourcing` in your entire project
