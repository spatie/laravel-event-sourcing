# Changelog

All notable changes to `laravel-event-sourcing` will be documented in this file:

## 6.0.1 - 2021-11-26

- Fix for aggregate root testing without a database #292

## 6.0.0 - 2021-11-24

- Support PHP 8.1
- The `EventHandler` interface was changed in order to use the `spatie/better-types` package:

```diff
-    public function handles(): array;
+    public function handles(StoredEvent $storedEvent): bool;

-    public function handle(StoredEvent $event);
+    public function handle(StoredEvent $storedEvent): void;

```
## 6.0.0 - 2021-??-??

- Support PHP 8.1
- The `EventHandler` interface was changed in order to use the `spatie/better-types` package:

```diff
-    public function handles(): array;
+    public function handles(StoredEvent $storedEvent): bool;

-    public function handle(StoredEvent $event);
+    public function handle(StoredEvent $storedEvent): void;

```
## 5.0.8 - 2021-11-17

## What's Changed

- Fixed tests/VersionedEventTest.php::a_versioned_event_can_be_restored  by @etahamer in https://github.com/spatie/laravel-event-sourcing/pull/286
- Set minimum version of illuminate/database to ^8.34 by @etahamer in https://github.com/spatie/laravel-event-sourcing/pull/290

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/5.0.7...5.0.8

## 5.0.7 - 2021-11-17

## What's Changed

- Update introduction.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/266
- Update installation-setup.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/265
- Update introduction.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/264
- Update using-projectors-to-transform-events.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/263
- Update using-aggregates-to-make-decisions-based-on-the-past.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/262
- Update creating-and-configuring-projectors.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/261
- Update thinking-in-events.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/260
- Update writing-your-first-reactor.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/259
- Update writing-your-first-aggregate.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/258
- Update replaying-events.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/257
- Update storing-metadata.md by @WouterBrouwers in https://github.com/spatie/laravel-event-sourcing/pull/256
- fix broken link to the course by @macbookandrew in https://github.com/spatie/laravel-event-sourcing/pull/253
- Fix urls pointing to previous version by @quintenbuis in https://github.com/spatie/laravel-event-sourcing/pull/269
- [Docs] Add `EloquentStoredEvent` import to example by @stevebauman in https://github.com/spatie/laravel-event-sourcing/pull/273
- [Docs] Add missing opening bracket for `Account` model by @stevebauman in https://github.com/spatie/laravel-event-sourcing/pull/272
- [Docs] Fix wrong operator for onMoneySubtracted by @avosalmon in https://github.com/spatie/laravel-event-sourcing/pull/279
- Changed cursor() into lazyById() to preserve memory when working with large amount of events by @etahamer in https://github.com/spatie/laravel-event-sourcing/pull/284

## New Contributors

- @WouterBrouwers made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/266
- @macbookandrew made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/253
- @quintenbuis made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/269
- @stevebauman made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/273
- @avosalmon made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/279
- @etahamer made their first contribution in https://github.com/spatie/laravel-event-sourcing/pull/284

**Full Changelog**: https://github.com/spatie/laravel-event-sourcing/compare/5.0.6...5.0.7

## 5.0.6 - 2021-09-12

- fix AggregateRoot return types for static analysis (#251)

## 5.0.5 - 2021-07-26

- Use `jsonb` in migration stubs instead of `json` (#237)

## 5.0.4 - 2021-06-15

- Fix visual glitch in `event-sourcing:list` command where event handlers wouldn't be shown

## 5.0.3 - 2021-06-14

- fix `$whenResult` (#227)

## 5.0.2 - 2021-06-14

- Support legacy `spatie/laravel-schemaless-attributes:^1.0` as well

## 5.0.1 - 2021-06-10

- move migrations to default location

## 5.0.0 - 2021-06-09

- Add `EloquentStoredEvent::query()-&amp;amp;gt;whereEvent(EventA::class, …)`
- 
- Add `EventQuery`
- 
- Add `AggregatePartial`
- 
- - If you're overriding an aggregate root's constructor, make sure to call `parent::__construct` from it
- 
- 
- 
- Add command bus and aggregate root handlers
- 
- Add `Projectionist::fake(OriginalReactor::class, FakeReactor::class)` ([#181](https://github.com/spatie/laravel-event-sourcing/discussions/181))
- 
- All event listeners are now registered in the same way: by looking at an event's type hint. This applies to all:
- 
- - Aggregate root `apply` methods
- 
- 
- - Projection listeners
- 
- 
- - Reactor listeners
- 
- 
- - Event queries
- 
- 
- 
- Moved `Spatie\EventSourcing\Exception\CouldNotPersistAggregate` to `Spatie\EventSourcing\AggregateRoots\Exceptions\CouldNotPersistAggregate`
- 
- Moved `Spatie\EventSourcing\Exception\InvalidEloquentSnapshotModel` to `Spatie\EventSourcing\AggregateRoots\Exceptions\InvalidEloquentSnapshotModel`
- 
- Moved `Spatie\EventSourcing\Exception\InvalidEloquentStoredEventModel` to `Spatie\EventSourcing\AggregateRoots\Exceptions\InvalidEloquentStoredEventModel`
- 
- Moved `Spatie\EventSourcing\Exception\MissingAggregateUuid` to `Spatie\EventSourcing\AggregateRoots\Exceptions\MissingAggregateUuid`
- 
- Moved `Spatie\EventSourcing\Exception\InvalidStoredEvent` to `Spatie\EventSourcing\StoredEvents\Exceptions\InvalidStoredEvent`
- 
- Dependency injection in handlers isn't supported anymore,  use constructor injection instead
- 
- `$storedEvent` and `$aggregateRootUuid` are no longer passed to event handler methods. Use `$event-&amp;amp;gt;storedEventId()` and `$event-&amp;amp;gt;aggregateRootUuid()` instead. ([#180](https://github.com/spatie/laravel-event-sourcing/discussions/180))
- 
- Rename `EloquentStoredEvent::query()-&amp;amp;gt;uuid()` to `EloquentStoredEvent::query()-&amp;amp;gt;whereAggregateRoot()`
- 
- Removed `AggregateRoot::$allowConcurrency`
- 
- Removed `$aggregateVersion` from `StoredEventRepository::persist`
- 
- Removed `$aggregateVersion` from `StoredEventRepository::persistMany`
- 
- Event handlers are no longer called with `app()-&amp;amp;gt;call()` ([#180](https://github.com/spatie/laravel-event-sourcing/discussions/180))
- 
- `$handlesEvents` on Projectors and Reactors isn't supported anymore
- 
- PHP version requirement is now `^8.0`
- 
- Laravel version requirement is now `^8.0`
- 

### A note on changed listeners

Since most code is probably already type hinting events, the listener change is likely to not have an impact on your code. It's good to know though that you don't have to worry about certain naming conventions any more:

- In **aggregate roots**, you don't have to prefix apply methods with `apply` anymore if you don't want to
- In **projectors**, you don't need a manual mapping anymore, neither does the event variable need to be called `$event`
- In **reactors**, you don't need a manual mapping anymore, neither does the event variable need to be called `$event`
- **Event queries** are a new concept and work in the same way

Here's an example:

```php
class MyProjector extends Projector
{
    public function anEventHandlerWithAnotherName(MyEvent $eventVariableWithAnotherName): void
    {
        // This handler will automatically handle `MyEvent`
    }
}



```
Note that `__invoke` in projectors and reactors works the same way, it's automatically registered based on the type hinted event.

## 4.10.2 - 2021-05-04

- Add missing config key in config stub (#203)

## 4.10.1 - 2021-04-21

- Also store aggregate root version when one event is persisted

## 4.10.0 - 2021-04-21

- Deprecate `AggregateRoot::$allowConcurrency`
- Fix for race condition in aggregate roots (#170), you will need to run a migration to be able to use it:

```php
public function up()
{
    Schema::table('stored_events', function (Blueprint $table) {
        $table->unique(['aggregate_uuid', 'aggregate_version']);
    });
}



```
**Note**: if you run this migration, all aggregate roots using `$allowConcurrency` will not work any more.

## 4.9.0 - 2021-03-10

- Make base path configurable (#202)

## 4.8.0 - 2021-01-28

- Add support for asserting events with a closure

## 4.7.2 - 2021-01-28

- Fix for broken dependency in 4.7.1

## 4.7.1 - 2021-01-21

- Fix for array serialization (#189)

## 4.7.0 - 2020-12-02

- add support for PHP 8

## 4.6.1 - 2020-10-23

- remove unused `replay_chunk_size` config value

## 4.6.0 - 2020-10-21

- allow protected apply methods (#136)

## 4.5.3 - 2020-10-02

- re-use existing instance of `ShouldBeStored` when possible (#158)

## 4.5.2 - 2020-10-02

- fix Paths and Reference URL in event-sourcing.php config file (#159)

## 4.5.1 - 2020-09-27

- added `loadUuid` (#156)

## 4.5.0 - 2020-09-25

- make normalizers configurable (#153)

## 4.4.0 - 2020-09-18

- Support `then` for aggregate root fakes (#154)

## 4.3.1 - 2020-09-09

- Support Laravel 8

## 4.3.0 - 2020-08-24

- support Carbon dates in events (#137)

## 4.2.0 - 2020-08-06

- allow events to be dispatched from an aggregate root (#135)

## 4.1.0 - 2020-08-03

- add assertion that specific event is recorded (#134)

## 4.0.2 - 2020-07-29

- config style fix

## 4.0.1 - 2020-07-29

- add `snapshot_model` config key

## 4.0.0 - 2020-07-22

- projectors now are abstract classes instead of interfaces
- reactors can now be easily defined by extending the reactor base class
- projectors and reactors can be marked as async by implementing the `ShouldQueue` marker interface
- events that extend `ShouldBeStored` now can retrieve the aggregate root uuid using `aggregateRootUuid()`
- the package has been restructured. Namespaces of most classes have been updated.
- events that extend `ShouldBeStored` can now handle metadata using `metaData` and `setMetaData`
- aggregate roots can now be persisted without calling event handlers using `persistWithoutApplyingToEventHandlers`
- the projectionist can now handle manually specified events using `handleStoredEvents`
- added `persistAggregateRootsInTransaction` to `AggregateRoot`
- you can now get the `uuid` of an aggregate root using the `uuid()` method
- the `reset` method has been removed on projectors
- the `fake` method on an aggregate root now accepts a uuid instead of an array of events
- the `meta_data` property on `StoredEvent` is now an array or a string instead of `SchemalessAttributes`
- apply methods on aggregates can now make use of method injection
- pass metadata to serializer to allow events to be upgraded (#112)

## 3.2.3 - 2020-07-14

- default to `BigIncrements` on package table stubs (#124)

## 3.2.2  - 2020-07-14

- replace model where clause with uuid model scope (#123)

## 3.2.1 - 2020-07-09

- config file comment corrections (#121)

## 3.2.0 - 2020-06-30

- expose `aggregate_version` of `StoredEvent` (#115)

## 3.1.8 - 2020-06-28

- use `app` helper (#117)

> > > > > > > master

## 3.1.7 - 2020-06-18

- allow aggregate roots to have dependencies in constructor (#111)

## 3.1.6 - 2020-06-17

- wrong tag, nothing changed

## 3.1.5 - 2020-05-23

- only include public properties of the aggregate when snapshotting (#105)

## 3.1.4 - 2020-05-07

- simplify snapshot dates

## 3.1.3 - 2020-04-29

- add `static` return type docblock for `AggregateRoot::retrieve`

## 3.1.2 - 2020-04-07

- make sure `created_at` is filled when creating a snapshot

## 3.1.1 - 2020-03-21

- expose `AggregateRoot` for testing state (#75)

## 3.1.0 - 2020-03-03

- add support for Laravel 7

## 3.0.4 - 2020-02-23

- fix for serializing events that use immutable datetime objects (#67)

## 3.0.3 - 2020-02-18

- fixes for Lumen

## 3.0.2 - 2020-02-14

- only replace the first instance of the `basePath` in `DiscoversEventHandlers` (#62)

## 3.0.1 - 2020-02-14

- publish snapshots migration

## 3.0.0 - 2020-02-07

- add the ability to snapshot aggregates
- make all classes non-final
- do not allow concurrent persist on an aggregate by default

## 2.1.0 - 2020-02-07

- add `countAllStartingFrom`

## 2.0.1 - 2020-01-20

- do not dispatch job when there is nothing to be performed on queue

## 2.0.0 - 2019-12-02

- drop PHP 7.3

## 1.0.4 - 2019-11-20

- fix replay from specified event id (#33)

## 1.0.3 - 2019-11-01

- provide docblocks to AggregateRoot class (#31)

## 1.0.2 - 2019-10-27

- implemented missing HandleDomainEventJob interface
- use a UUID field when possible for storing UUIDs

## 1.0.1 - 2019-10-11

- fix an issue with encoding the `event_properties` when they're already a string

## 1.0.0 - 2019-09-20

- initial release

This package supercedes [spatie/laravel-event-projector](https://github.com/spatie/laravel-event-projector)

To learn how to upgrade from laravel-event-projector v3 to laravel-event-sourcing v1 , read [our upgrade guide](https://github.com/spatie/laravel-event-sourcing/blob/master/UPGRADING.md)
