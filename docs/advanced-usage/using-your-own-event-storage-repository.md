---
title: Using your own event storage repository
weight: 6
---

The default repository responsible for storing events is `\Spatie\EventProjector\EloquentStoredEventRepository`. If you want to use a different storage method, implement the `\Spatie\EventProjector\StoredEventRepository` repository and add your custom functionality.

If you just want to use a different model for the Eloquent storage:

1. Create your own Eloquent model and extend the `\Spatie\EventProjector\Models\EloquentStoredEvent::class` class
2. Extend the `\Spatie\EventProjector\EloquentStoredEventRepository` and change the `$storedEventModel` property with your own model that extends `\Spatie\EventProjector\Models\EloquentStoredEvent`
3. Change the `stored_event_repository` config value with your own repository in the `event-projector.php` config file.
