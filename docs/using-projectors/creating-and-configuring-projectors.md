---
title: Creating and registering projectors
weight: 2
---

A projector is a class that listens for events that were stored. When it hears an event that it is interested in, it can perform some work.

## Creating projectors

Let's create a projector. You can perform this artisan command to create a projector in `app\Projectors`:

```php
php artisan make:projector AccountBalanceProjector
```

## Registering projectors

By default, the package will automatically find and register all projectors found in your application.

Alternatively, you can manually register projectors in the `projectors` key of the `event-sourcings` config file.

You can also add them to the `Projectionist`. This can be done anywhere, but typically you would do this in a ServiceProvider of your own.

```php
namespace App\Providers;

use App\Projectors\AccountBalanceProjector;
use Illuminate\Support\ServiceProvider;
use Spatie\EventSourcing\Facades\Projectionist;

class EventSourcingServiceProvider extends ServiceProvider
{
    public function register()
    {
        // adding a single projector
        Projectionist::addProjector(AccountBalanceProjector::class);

        // you can also add multiple projectors in one go
        Projectionist::addProjectors([
            AnotherProjector::class,
            YetAnotherProjector::class,
        ]);
    }
}
```

## Using projectors

This is the contents of a class created by the artisan command mentioned in the section above.

```php
namespace App\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class MyProjector extends Projector
{
    public function onEventHappened(EventHappened $event)
    {
        // do some work
    }
}
```

Just by adding a typehint of the event you want to handle makes our package call that method when the typehinted event occurs. All methods specified in your projector can also make use of method injection, so you can resolve any dependencies you need in those methods as well.

## Getting the uuid of an event

In most cases you want to have access to the event that was fired. When [using aggregates](/docs/laravel-event-sourcing/v7/using-aggregates/writing-your-first-aggregate) your events probably won't contain the uuid associated with that event. To get to the uuid of an event simply call the `aggregateRootUuid()` method on the event object.

```php
// ...

public function onMoneyAdded(MoneyAdded $event)
{
    $account = Account::findByUuid($event->aggregateRootUuid());

    $account->balance += $event->amount;

    $account->save();
}
```

## Manually registering event handling methods

The `$handlesEvents` property is an array which has event class names as keys and method names as values. Whenever an event is fired that matches one of the keys in `$handlesEvents` the corresponding method will be fired. You can name your methods however you like.

Here's an example where we listen for a `MoneyAdded` event:

```php
namespace App\Projectors;

use App\Account;
use App\Events\MoneyAdded;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class AccountBalanceProjector extends Projector
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

When the package needs to call the projector, it will use the container to create that projector so you may inject any dependencies in the constructor. In fact, all methods specified in `$handlesEvent` can make use of method injection, so you can resolve any dependencies you need in those methods as well. Any variable in the method signature with the name `$event` will receive the event you're listening for.

## Using a class as an event handler

Instead of letting a method on a projector handle an event you can use a dedicated class.

```php
// in a projector

// ...

protected array $handlesEvents = [
    /*
     * If this event is passed to the projector, the `AddMoneyToAccount` class will be called.
     */
    MoneyAdded::class => AddMoneyToAccount::class,
];
```

Here's an example implementation of `AddMoneyToAccount`:

```php
use App\Events\MoneyAdded;

class AddMoneyToAccount
{
    public function __invoke(MoneyAdded $event)
    {
        $event->account->addMoney($event->amount);
    }
}
```

## Using default event handling method names

In the example above the events are mapped to methods on the projector using the `$handlesEvents` property.

```php
// in a projector

// ...

protected array $handlesEvents = [
    MoneyAdded::class => 'onMoneyAdded',
];
```

You can write this a little shorter. Just put the class name of an event in that array. The package will infer the method name to be called. It will assume that there is a method called `on` followed by the name of the event. Here's an example:

```php
// in a projector

// ...

protected array $handlesEvents = [
    /*
     * If this event is passed to the projector, the `onMoneyAdded` method will be called.
     */
    MoneyAdded::class,
];
```

## Handling a single event

You can `$handleEvent` to the class name of an event. When such an event comes in we'll call the `__invoke` method.

```php
// in a projector

// ...

protected $handleEvent =  MoneyAdded::class,

public function __invoke(MoneyAdded $event)
{
}
```

## Want to know more?

We discuss projections and complex patterns such as CQRS in depth in our [Event Sourcing in Laravel](https://event-sourcing-laravel.com/) course. In practice, you want to check out these chapters:

- 05. Storing and Projecting Events
- 06. [Projectors in Depth](https://event-sourcing-laravel.com/projectors-in-depth)
- 14. CQRS
