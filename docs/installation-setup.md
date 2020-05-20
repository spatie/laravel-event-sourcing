---
title: Installation & setup
weight: 4
---

laravel-event-sourcing can be installed via composer:

```bash
composer require spatie/laravel-event-sourcing
```

You need to publish and run the migrations to create the `stored_events` table:

```bash
php artisan vendor:publish --provider="Spatie\EventSourcing\EventSourcingServiceProvider" --tag="migrations"
php artisan migrate
```

You must publish the config file with this command:

```bash
php artisan vendor:publish --provider="Spatie\EventSourcing\EventSourcingServiceProvider" --tag="config"
```

This is the default content of the config file that will be published at `config/event-sourcing.php`:

```php
return [

    /*
     * These directories will be scanned for projectors and reactors. They
     * will be automatically registered to projectionist automatically.
     */
    'auto_discover_projectors_and_reactors' => [
        app_path(),
    ],

    /*
     * Projectors are classes that build up projections. You can create them by performing
     * `php artisan event-sourcing:create-projector`.  When not using auto-discovery
     * Projectors can be registered in this array or a service provider.
     */
    'projectors' => [
        // App\Projectors\YourProjector::class
    ],

    /*
     * Reactors are classes that handle side effects. You can create them by performing
     * `php artisan event-sourcing:create-reactor`. When not using auto-discovery
     * Reactors can be registered in this array or a service provider.
     */
    'reactors' => [
        // App\Reactors\YourReactor::class
    ],

    /*
     * A queue is used to guarantee that all events get passed to the projectors in
     * the right order. Here you can set of the name of the queue.
     */
    'queue' => env('EVENT_PROJECTOR_QUEUE_NAME', null),

    /*
     * When a projector or reactor throws an exception the event projectionist can catch it
     * so all other projectors and reactors can still do their work. The exception will
     * be passed to the `handleException` method on that projector or reactor.
     */
    'catch_exceptions' => env('EVENT_PROJECTOR_CATCH_EXCEPTIONS', false),

    /*
     * This class is responsible for storing events. To add extra behaviour you
     * can change this to a class of your own. The only restriction is that
     * it should extend \Spatie\EventSourcing\Models\StoredEvent.
     */
    'stored_event_model' => \Spatie\EventSourcing\Models\StoredEvent::class,

    /*
     * This class is responsible for handle stored events. To add extra behaviour you
     * can change this to a class of your own. The only restriction is that
     * it should implement \Spatie\EventSourcing\HandleDomainEventJob.
     */
    'stored_event_job' => \Spatie\EventSourcing\HandleStoredEventJob::class,

    /*
     * Similar to Relation::morphMap() you can define which alias responds to which
     * event class. This allows you to change the namespace or classnames
     * of your events but still handle older events correctly.
     */
    'event_class_map' => [],

    /*
     * This class is responsible for serializing events. By default an event will be serialized
     * and stored as json. You can customize the class name. A valid serializer
     * should implement Spatie\EventSourcing\EventSerializers\Serializer.
     */
    'event_serializer' => \Spatie\EventSourcing\EventSerializers\JsonEventSerializer::class,

    /*
     * When replaying events potentially a lot of events will have to be retrieved.
     * In order to avoid memory problems events will be retrieved in
     * a chunked way. You can specify the chunk size here.
     */
    'replay_chunk_size' => 1000,

    /*
     * In production, you likely don't want the package to auto discover the event handlers
     * on every request. The package can cache all registered event handlers.
     * More info: https://docs.spatie.be/laravel-event-sourcing/v3/advanced-usage/discovering-projectors-and-reactors
     *
     * Here you can specify where the cache should be stored.
     */
    'cache_path' => storage_path('app/event-sourcing'),
];
```

The package will scan all classes of your project to [automatically discover projectors and reactors](/laravel-event-sourcing/v3/advanced-usage/discovering-projectors-and-reactors#discovering-projectors-and-reactors). In a production environment you probably should [cache auto discovered projectors and reactors](/laravel-event-sourcing/v3/advanced-usage/discovering-projectors-and-reactors#caching-discovered-projectors-and-reactors).

It's recommended that you set up a queue. Specify the connection name in the `queue` key of the `event-sourcing` config file. This queue will be used to guarantee that the events will be processed by all projectors in the right order. You should make sure that the queue will process only one job at a time. In a local environment, where events have a very low chance of getting fired concurrently, it's probably ok to just use the `sync` driver.

When using [Laravel Horizon](https://laravel.com/docs/horizon) you can update your `horizon.php` config file as such:

```php
'environments' => [
    'production' => [

        // ...

        'event-sourcing-supervisor-1' => [
            'connection' => 'redis',
            'queue' => [env('EVENT_PROJECTOR_QUEUE_NAME')],
            'balance' => 'simple',
            'processes' => 1,
            'tries' => 3,
        ],
    ],

    'local' => [

        // ...

        'event-sourcing-supervisor-1' => [
            'connection' => 'redis',
            'queue' => [env('EVENT_PROJECTOR_QUEUE_NAME')],
            'balance' => 'simple',
            'processes' => 1,
            'tries' => 3,
        ],
    ],
],
```

