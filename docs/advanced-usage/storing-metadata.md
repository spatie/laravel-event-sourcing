---
title: Storing metadata
weight: 2
---

You can add metadata, such as the `id` of the logged in user, to a stored event. 

## Storing metadata on all events

If you need to store metadata on all events you can leverage Laravel's native models events.

You must configure the package [use your own event storage model](/laravel-event-projector/v2/advanced-usage/using-your-own-event-storage-model). On that model you can hook into the model lifecycle hooks.

```php
use Spatie\EventProjector\Models\StoredEvent;

class CustomStoredEvent extends StoredEvent
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

The `StoredEvent` instance will be passed on to any projector method that has a variable named `$storedEvent`. On that `StoredEvent` instance there is a property, `meta_data`, that returns an instance of `Spatie\SchemalessAttributes\SchemalessAttributes`.

Here's an example:

```php
namespace App\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Facades\Projectionist;
use App\Events\MoneyAdded;

class MetaDataProjector implements Projector
{
    use ProjectsEvents;

    /*
     * Here you can specify which event should trigger which method.
     */
    public $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
    ];

    public function onMoneyAdded(StoredEvent $storedEvent)
    {
    
        if (Projectionist::isReplaying()) {
           $storedEvent->meta_data['user_id'] = auth()->user()->id;

           $storedEvent->save();
        }
        
        // ...
    }
}
```
