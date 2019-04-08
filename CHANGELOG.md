# Changelog

All notable changes to `laravel-event-projector` will be documented in this file

## 2.0.0 - 2019-04-XX

- added support for aggregates
- support a new `handleEvent` property for event handlers
- removed all support for projector statusses
- the `rebuild` command has been removed. It's been replace by the `--from` flag on event replay

## 1.3.2 - 2018-02-27

- add support for Laravel 5.8

# 1.3.1 - 2018-12-06

- fix missing `use` statement in `EventHandlerCollection`

## 1.3.0 - 2018-11-19

- add `stored_event_job` to config file

## 1.2.0 - 2018-10-30

- add `isProjecting`

## 1.1.2 - 2018-09-27

- fix for working with a custom `StoredEvent`, `ProjectorStatus`

## 1.1.1 - 2018-09-01

- add support for Laravel 5.7

## 1.1.0 - 2018-09-01

- add ability to add tags to be displayed in Horizon

## 1.0.5 - 2018-08-13

- moaarrr fixes for replaying events using a custom `StoredEvent` model

## 1.0.4 - 2018-08-13

- fix replaying events using a custom `StoredEvent` model

## 1.0.3 - 2018-08-05

- fixed some typos in the config file

## 1.0.2 - 2018-07-29

- fix name of used env variable that determines the queue name

## 1.0.1 - 2018-07-28

- fix reactor stub

## 1.0.0 - 2018-07-13

- initial release
