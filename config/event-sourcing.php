<?php

return [

    /*
     * These directories will be scanned for projectors and reactors. They
     * will be registered to Projectionist automatically.
     */
    'auto_discover_projectors_and_reactors' => [
        app_path(),
    ],

    /*
     * Projectors are classes that build up projections. You can create them by performing
     * `php artisan event-sourcing:create-projector`. When not using auto-discovery,
     * Projectors can be registered in this array or a service provider.
     */
    'projectors' => [
        // App\Projectors\YourProjector::class
    ],

    /*
     * Reactors are classes that handle side-effects. You can create them by performing
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
     * When a Projector or Reactor throws an exception the event Projectionist can catch it
     * so all other projectors and reactors can still do their work. The exception will
     * be passed to the `handleException` method on that Projector or Reactor.
     */
    'catch_exceptions' => env('EVENT_PROJECTOR_CATCH_EXCEPTIONS', false),

    /*
     * This class is responsible for storing events in the EloquentStoredEventRepository.
     * To add extra behaviour you can change this to a class of your own. It should
     * extend the \Spatie\EventSourcing\Models\EloquentStoredEvent model.
     */
    'stored_event_model' => \Spatie\EventSourcing\Models\EloquentStoredEvent::class,

    /*
     * This class is responsible for storing events. To add extra behaviour you
     * can change this to a class of your own. The only restriction is that
     * it should implement \Spatie\EventSourcing\StoredEventRepository.
     */
    'stored_event_repository' => \Spatie\EventSourcing\EloquentStoredEventRepository::class,

    /*
     * This class is responsible for handling stored events. To add extra behaviour you
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
     * When replaying events, potentially a lot of events will have to be retrieved.
     * In order to avoid memory problems events will be retrieved as chunks.
     * You can specify the chunk size here.
     */
    'replay_chunk_size' => 1000,

    /*
     * In production, you likely don't want the package to auto-discover the event handlers
     * on every request. The package can cache all registered event handlers.
     * More info: https://docs.spatie.be/laravel-event-sourcing/v1/advanced-usage/discovering-projectors-and-reactors
     *
     * Here you can specify where the cache should be stored.
     */
    'cache_path' => storage_path('app/event-sourcing'),
];
