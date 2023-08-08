---
title: Using aliases for stored event classes
weight: 9
---

By default we store the `Event`'s FQCN in the database when storing the events. This prevents you from changing the name or the namespace of your event classes.

To get around this you can define event class aliases in the `event-sourcing.php` config file:

```php
    /*
     * Storable events are type of events being stored in storage repository when they fire.
     * You can create them by performing `php artisan make:storable-event`.
     * Similar to Relation::morphMap() you can define which alias responds to which
     * event class. This allows you to change the namespace or class names
     * of your events but still handle older events correctly.
     */
    'storable_events' => [
        'money-added' => App\StorableEvents\MoneyAddedEvent::class,
    ],
```

With this configuration, instead of saving `\App\Events\MoneyAddedEvent` in the database, it just stores `money_added` so you can change the event's classname and namespace. Just make sure to also change the mapping!
