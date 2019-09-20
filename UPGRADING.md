# Upgrading

Because there are many breaking changes we cannot give you a waterproof list of steps to provide. There are many edge cases this guide does not cover. We accept PRs to improve this guide.

## From v3 to v4

The only change in this version is the naming change from `laravel-event-projector` to `laravel-event-sourcing`

To upgrade you just have to perform these steps:
1. rename `config/laravel-event-projector.php` to `config/laravel-event-sourcing.php`
2. `laravel-event-projector:v3` to `laravel-event-sourcing:v3` and run `composer update`
3. The namespace has changed, so you need to replace `Spatie\EventSourcing` by `Spatie\EventSourcing` in your entire project

## From v2 to v3

- Add a `stored_event_repository` config key with the following value: `\Spatie\EventSourcing\EloquentStoredEventRepository::class`
- If you're using a different model for event storage:
    1. Make sure the model extends `\Spatie\EventSourcing\Models\EloquentStoredEvent`


## From v1 to v2

- Add a nullable `aggregate_uuid` field in the `stored_events` table
- Delete the `projector_statuses` table
- Remove all options in the config file not present in the config file that ships with v2
- In v1 streams were used to track if events came in the right order.  All support for event streams has been removed. If for your projectors the order of events is imports, use a queued projector.
- v1 tracked which events were already processed by a given event handler. In v2 all functionality around projector statusses is removed. It's now your own resposibility that you give all projectors the right events. 

