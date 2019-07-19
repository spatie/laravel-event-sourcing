# Changelog

All notable changes to `laravel-event-projector` will be documented in this file

## 2.8.0 - 2019-07-19

- added aggregate test methods

## 2.7.0 - 2019-07-18

- added `event_class_map` to alias your event classes which allows for refactoring after events have been fired

## 2.6.3 - 2019-06-14

- fix warnings in console commands

## 2.6.2 - 2019-06-04

- fix stubs

## 2.6.1 - 2019-05-16

- fix for disabling the event listener auto discovery

## 2.6.0 - 2019-05-06

- add `stored-event-model` option to the replay command

## 2.5.0 - 2019-05-06

- allow to specify the model to be used by an aggregate for retrieving/peristing events by adding a `$storedEventModel` on it

## 2.4.0 - 2019-04-23

- allow to specify the queue that should be used on the event

## 2.3.0 - 2019-04-16

- add `list` command

## 2.2.0 - 2019-04-16

- automatically register event handlers

## 2.1.1 - 2019-04-13

- stubs don't use `handlesEvents` anymore

## 2.1.0 - 2019-04-13

- add autodetection for event handling methods

## 2.0.5 - 2019-04-09

- fix storable event command

## 2.0.4 - 2019-04-09

- fix `make` commands

## 2.0.3 - 2019-04-09

- make service provider final

## 2.0.2 - 2019-04-09

- make service provider non final

## 2.0.1 - 2019-04-09

- fix `HandleStoredEventJob`

## 2.0.0 - 2019-04-08

- added support for aggregates
- support a new `handleEvent` property for event handlers
- removed all support for projector statusses
- the `rebuild` command has been removed. It's been replace by the `--from` flag on event replay

## 1.3.2 - 2018-02-27

- add support for Laravel 5.8

## 1.3.1 - 2018-12-06

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
