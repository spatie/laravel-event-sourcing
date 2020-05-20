# Changelog

All notable changes to `laravel-event-sourcing` will be documented in this file:

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

- fix for serializing events that use immutable datatime objects (#67)

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
