# Upgrading

Because there are many breaking changes we cannot give you a waterproof list of steps to provide. There are many edge cases this guide does not cover. We accept PRs to improve this guide.

## From laravel-event-projector v3 to v1 of laravel-event-sourcing

The only change in this version is the naming change from `laravel-event-projector` to `laravel-event-sourcing`

To upgrade from v3 of `laravel-event-projector` you have to perform these steps:
1. rename `config/laravel-event-projector.php` to `config/laravel-event-sourcing.php`
2. `laravel-event-projector:v3` to `laravel-event-sourcing:v1` and run `composer update`
3. The namespace has changed, so you need to replace `Spatie\EventSourcing` by `Spatie\EventSourcing` in your entire project
