---
title: Making sure events get handled in the right order
weight: 4
---

By default all events are handled in a synchronous manner. This means that if you fire off an event in a request, all projectors will get called in the same request.

## Handling events in a queue

A queue can be used to guarantee that all events get passed to projectors in the right order. If you want a projector to handle events in a queue then simply add the `Illuminate\Contracts\Queue\ShouldQueue` interface to your projector just like you would a Job. 

A useful rule of thumb is that if your projectors aren't producing data that is consumed in the same request as the events are fired, you should let your projector implement `Illuminate\Contracts\Queue\ShouldQueue`.

You can set the name of the queue connection in the `queue` key of the `event-sourcing` config file.  You should make sure that the queue will process only one job at a time.

In a local environment, where events have a very low chance of getting fired concurrently, it's probably ok to just use the `sync` driver.

## Tweaking projector order

You can add a getWeight method to a projector to tweak the order projectors are run in. Projectors with a lower weight run first. When no explicit weight is provided, the weight is considered `0`.

```php
namespace App\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

class MyProjector extends Projector
{
    public function getWeight(?StoredEvent $event): int 
    {
        return 5;
    }
    
    //
}
```

Alternatively, you can determine the weight dynamically based on the event being processed. This allows you to prioritize certain events over others. The `$event` parameter will be `null` when the `resetState()` method is called (during projector reset operations).

```php
namespace App\Projectors;

use App\Events\MoneyAddedEvent;
use App\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

class MyProjector extends Projector
{
    public function getWeight(?StoredEvent $event): int
    {
        return match ($event?->event_class) {
            MoneyAddedEvent::class => 2,
            MoneySubtractedEvent::class => -2,
            default => 0,
        };
    }
    
    //
}
```

Note that providing a weight on a queued projector won't guarantee execution order.

## Want to know more?

We discuss projections and complex patterns such as CQRS in depth in our [Event Sourcing in Laravel](https://event-sourcing-laravel.com/) course. In practice, you want to check out these chapters:

- 05. Storing and Projecting Events
- 06. [Projectors in Depth](https://event-sourcing-laravel.com/projectors-in-depth)
- 14. CQRS
