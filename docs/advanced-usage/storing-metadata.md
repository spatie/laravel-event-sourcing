---
title: Storing metadata
weight: 3
---

You can add metadata, such as the `id` of the logged in user, to a stored event. 

## Storing metadata on all events

If you need to store metadata on all events you can leverage Laravel's native models events when using the `EloquentStoredEventRepository`.

You must configure the package to [use your own eloquent event storage model](/docs/laravel-event-sourcing/v7/advanced-usage/using-your-own-event-storage-model) that extends the `EloquentStoredEvent` model. On that model you can hook into the model lifecycle hooks.

```php
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

class CustomStoredEvent extends EloquentStoredEvent
{
    public static function boot()
    {
        parent::boot();
        
        static::creating(function(CustomStoredEvent $storedEvent) {
            $storedEvent->meta_data['user_id'] = auth()->user()->id;
        });
    }
}
```

## Storing metadata via a projector

The `StoredEvent` instance will be passed on to any projector method that has a variable named `$storedEvent`. You'll also need the `StoredEventRepository` that is used by the application to update the stored event. 
On the `StoredEvent` instance there is a property, `meta_data`, that returns an array. You can update this array to store any metadata you like.

Here's an example:

```php
namespace App\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Models\StoredEvent;
use Spatie\EventSourcing\Facades\Projectionist;
use App\Events\MoneyAdded;

class MetaDataProjector extends Projector
{
    /*
     * Here you can specify which event should trigger which method.
     */
    public array $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
    ];

    public function onMoneyAdded(StoredEvent $storedEvent, StoredEventRepository $repository)
    {
        if (! Projectionist::isReplaying()) {
            $storedEvent->meta_data['user_id'] = auth()->user()->id;

            $repository->update($storedEvent);
        }
        
        // ...
    }
}
```
