# Upgrading

## From v2 to v3

- Add an `aggregate_version` property to the `stored_events` table `$table->unsignedInteger('aggregate_version')->nullable();`
- Republish the migrations or copy the `create_snapshots_table` migration
- The `StoredEventRepository` interface has new methods called `retrieveAllAfterVersion` and `getLatestVersion` that you must implement if you have a custom repository
- The `StoredEventRepository` now accepts an `aggregateVersion` parameter in the `persist` and `persistMany` methods
- The `

## From v1 to v2

Nothing changed, expect that where possible property types and short closures are used

## From laravel-event-projector v3 to v1 of laravel-event-sourcing

The only change in this version is the naming change from `laravel-event-projector` to `laravel-event-sourcing`. There are no changes to the API.

To upgrade from v3 of `laravel-event-projector` you have to perform these steps:
1. Rename `config/event-projector.php` to `config/event-sourcing.php`
2. Change `laravel-event-projector:v3` to `laravel-event-sourcing:v1` and run `composer update`
3. The namespace has changed, so you need to replace `Spatie\EventProjector` by `Spatie\EventSourcing` in your entire project
