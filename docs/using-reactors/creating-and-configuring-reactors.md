---
title: Creating and configuring reactors
weight: 2
---

A reactor is a class, that much like a projector, listens for incoming events. Unlike projectors however, reactors will not get called when events are replayed. Reactors will only get called when the original event fires.

## Creating reactors

Let's create a reactor. You can perform this artisan command to create a reactor in `app\Reactors`:

```php
php artisan make:reactor BigAmountAddedReactor
```

## Registering reactors

By default, the package will automatically find and register all reactors found in your application.

Alternatively, you can also manually register them in the `reactors` key of the `event-sourcings` config file.

They can also be added to the `Projectionist`. This can be done anywhere, but typically you would do this in a ServiceProvider of your own.

```php
namespace App\Providers;

use App\Projectors\AccountBalanceProjector;
use Illuminate\Support\ServiceProvider;
use Spatie\EventSourcing\Facades\Projectionist;

class EventSourcingServiceProvider extends ServiceProvider
{
    public function register()
    {
        // adding a single reactor
        Projectionist::addReactor(BigAmountAddedReactor::class);

        // you can also add multiple reactors in one go
        Projectionist::addReactors([
            AnotherReactor::class,
            YetAnotherReactor::class,
        ]);
    }
}
```

## Using reactors

This is the contents of a class created by the artisan command mentioned in the section above:

```php
namespace App\Reactors;

class MyReactor
{
    public function onEventHappened(EventHappened $event)
    {

    }
}
```

Just by adding a typehint of the event you want to handle makes our package call that method when the typehinted event occurs. All methods specified in your reactor can also make use of method injection, so you can resolve any dependencies you need in those methods as well.

## Getting the uuid of an event

In most cases you want to have access to the event that was fired. When using aggregates your events probably won't contain the uuid associated with that event. To get to the uuid of an event simply call the `aggregateRootUuid()` method on the event object. 

```php
// ...

public function onMoneyAdded(MoneyAdded $event)
{
    $account = Account::findByUuid($event->aggregateRootUuid());
    
    Mail::to($account->user)->send(new MoreMoneyAddedMailable());
}
```

## Manually register event handling methods

The `$handlesEvents` property is an array which has event class names as keys and method names as values. Whenever an event is fired that matches one of the keys in `$handlesEvents` the corresponding method will be fired. You can name your methods however you like.

Here's an example where we listen for a `MoneyAdded` event:

```php
namespace App\Reactors;

use App\Events\MoneyAdded;

class BigAmountAddedReactor
{
    /*
     * Here you can specify which event should trigger which method.
     */
    protected array $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',

    ];

    public function onMoneyAdded(MoneyAdded $event)
    {
        // do some work
    }
}
```

This reactor will be created using the container so you may inject any dependency you'd like. In fact, all methods present in `$handlesEvent` can make use of method injection, so you can resolve any dependencies you need in those methods as well. Any variable in the method signature with the name `$event` will receive the event you're listening for.

## Using default event handling method names

In the example above the events are mapped to methods on the reactor using the `$handlesEvents` property.

```php
// in a reactor

// ...

protected array $handlesEvents = [
    MoneyAdded::class => 'onMoneyAdded',
];
```

You can write this a little shorter. Just put the class name of an event in that array. The package will infer the method name to be called. It will assume that there is a method called `on` followed by the name of the event. Here's an example:

```php
// in a reactor

// ...

protected array $handlesEvents = [
    /*
     * If this event is passed to the reactor, the `onMoneyAdded` method will be called.
     */ 
    MoneyAdded::class,
];
```

## Handling a single event

You can `$handleEvent` to the class name of an event. When such an event comes in we'll call the `__invoke` method. 

```php
// in a reactor

// ...

protected $handleEvent =  MoneyAdded::class,

public function __invoke(MoneyAdded $event)
{
}
```

## Using a class as an event handler

Instead of letting a method on a reactor handle an event you can use a dedicated class.

```php
// in a reactor

// ...

protected array $handlesEvents = [
    /*
     * If this event is passed to the reactor, the `AddMoneyToAccount` class will be called.
     */ 
    MoneyAdded::class => SendMoneyAddedMail::class,
];
```

Here's an example implementation of `SendMoneyAddedMail`:

```php
use App\Events\MoneyAdded;

class SendMoneyAddedMail
{
    public function __invoke(MoneyAdded $event)
    {
        // do work to send a mail here
    }
}
```

## Tweaking reactor order

You can add a getWeight method to a reactor to tweak the order reactors are run in. Reactors with a lower weight run first. When no explicit weight is provided, the weight is considered `0`.

```php
namespace App\Reactors;

use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

class MyReactor extends Reactor
{
    public function getWeight(?StoredEvent $event): int 
    {
        return 5;
    }
}
```

Alternatively, you can determine the weight dynamically based on the event being processed. This allows you to prioritize certain events over others.

```php
namespace App\Reactors;

use App\Events\MoneyAddedEvent;
use App\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

class MyReactor extends Reactor
{
    public function getWeight(?StoredEvent $event): int
    {
        return match ($event?->event_class) {
            MoneyAddedEvent::class => 2,
            MoneySubtractedEvent::class => -2,
            default => 0,
        };
    }
}
```

Note that providing a weight on a queued reactor won't guarantee execution order.

## Want to know more?

Reactors and process managers (which are built on top of the core reactor principle) are thoroughly discussed in [Event Sourcing in Laravel](https://event-sourcing-laravel.com/). More specifically, you want to read these chapters:

- 08. Reactors
- 15. Sagas
