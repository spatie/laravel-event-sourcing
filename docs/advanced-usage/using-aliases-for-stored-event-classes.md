---
title: Using aliases for stored event classes
weight: 9
---

By default we store the `Event`'s FQCN in the database when storing the events. This prevents you from changing the name or the namespace of your event classes.

To get around this you can define event class aliases in the `event-projector.php` config file:

```php
    /*
     * Similar to Relation::morphMap() you can define which alias responds to which
     * event class. This allows you to change the namespace or classnames
     * of your events but still handle older events correctly.
     */
    'event_class_map' => [
        'money_added' => MoneyAddedEvent::class,
    ],
```

With this configuration, instead of saving `\App\Events\MoneyAddedEvent` in the database, we just store `money_added`, now you can change the event class name and namespace. Just make sure to also change the mapping!
