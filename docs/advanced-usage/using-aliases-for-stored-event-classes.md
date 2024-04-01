---
title: Using aliases for stored event classes
weight: 9
---

By default we store the `Event`'s FQCN in the database when storing the events. This prevents you from changing the name or the namespace of your event classes.

To get around this you can define event class aliases in the `event-sourcing.php` config file:

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

With this configuration, instead of saving `\App\Events\MoneyAddedEvent` in the database, it just stores `money_added` so you can change the event's classname and namespace. Just make sure to also change the mapping!

If you want to make sure every event has an alias assigned you can enable the `enforce_event_class_map` option in the config: 

```php
    /*
     * Similar to Relation::enforceMorphMap() this option will make sure that every event has a
     * corresponding alias defined. Otherwise, an exception is thrown
     * if you try to persist an event without alias.
     */
    'enforce_event_class_map' => true,
```
