---
title: Storing metadata
weight: 3
---

You can add metadata, such as the `id` of the logged in user, to a stored event. 

## Storing metadata on all events

If you need to store metadata on all events you can leverage Laravel's native models events when using the `EloquentStoredEventRepository`.

You must configure the package to [use your own event storage repository](/laravel-event-projector/v3/advanced-usage/using-your-own-event-storage-repository) that extends the `EloquentStoredEventRepository` with a custom Eloquent model. On that model you can hook into the model lifecycle hooks.

```php
use Spatie\EventProjector\EloquentStoredEventRepository;

class CustomStoredEventRepository extends EloquentStoredEventRepository
{
    protected $storedEventModel = CustomStoredEvent::class;
}
```

```php
use Spatie\EventProjector\Models\EloquentStoredEvent;

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

    public function onMoneyAdded(StoredEvent $storedEvent, StoredEventRepository $repository)
    {
        if (Projectionist::isReplaying()) {
           $storedEvent->meta_data['user_id'] = auth()->user()->id;

           $repository->update($storedEvent);
        }
        
        // ...
    }
}
```
