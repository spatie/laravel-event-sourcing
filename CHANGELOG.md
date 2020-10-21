# Changelog

All notable changes to `laravel-event-sourcing` will be documented in this file:

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
>>>>>>> master

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
