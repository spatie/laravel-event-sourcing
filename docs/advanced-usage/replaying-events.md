---
title: Replaying events
weight: 2
---

All [events](/laravel-event-sourcing/v1/advanced-usage/preparing-events/) that implement `Spatie\EventSourcing\ShouldBeStored` will be [serialized](/laravel-event-sourcing/v1/advanced-usage/using-your-own-event-serializer) and stored in the `stored_events` table. After your app has been doing its work for a while the `stored_events` table will probably contain some events.

 When creating a new [projector](/laravel-event-sourcing/v1/using-projectors/writing-your-first-projector/) or [reactor](/laravel-event-sourcing/v1/using-reactors/writing-your-first-reactor/) you'll want to feed all stored events to that new projector or reactor. We call this process replaying events.

 Events can be replayed to [all projectors that were added to the projectionist](/laravel-event-sourcing/v1/using-projectors/creating-and-configuring-projectors/) with this artisan command:

 ```bash
 php artisan event-sourcing:replay
 ```

 You can also specify a single or multiple projectors. All stored events will be passed only to those projectors.

 ```bash
  php artisan event-sourcing:replay App\\Projectors\\AccountBalanceProjector
 ```

 When specifying multiple projectors, just chain them like this:

 ```bash
  php artisan event-sourcing:replay App\\Projectors\\AccountBalanceProjector App\\Projectors\\TransactionsCountProjector
 ```

If your projector has a `resetState` method it will get called before replaying events. You can use that method to reset the state of your projector.

If you want to replay events starting from a certain event you can use the `--from` option when executing `event-sourcing:replay`. If you use this option the `resetState` on projectors will not get called. This package does not track which events have already been processed by which projectors. Be sure not to replay events to projectors that already have handled them.

If you are [using your own event storage model](/laravel-event-sourcing/v1/advanced-usage/using-your-own-event-storage-model/) then you will need to use the `--stored-event-model` option when executing `event-sourcing:replay` to specify the model storing the events you want to replay.

```bash
php artisan event-sourcing:replay --stored-event-model=App\\Models\\AccountStoredEvent
 ```

## Detecting event replays

If your projector contains an `onStartingEventReplay` method, we'll call it right before the first event is replayed.

If it contains an `onFinishedEventReplay` method, we'll call it right after all events have been replayed.

You can also detect the start and end of event replay by listening for the `Spatie\EventSourcing\Events\StartingEventReplay` and `Spatie\EventSourcing\Events\FinishedEventReplay` events.

Though, under normal circumstances, you don't need to know this, you can detect if events are currently being replayed like this:

```php
Spatie\EventSourcing\Facades\Projectionist::isReplaying(); // returns a boolean
```

## Performing some work before and after replaying events

If your projector has a `onStartingEventReplay` method, it will get called right before the first event will be replayed. This can be handy to clean up any data your projector writes to. Here's an example where we truncate the `accounts` table before replaying events:

```php
namespace App\Projectors;

use App\Account;

// ...

class AccountBalanceProjector implements Projector
{
    use ProjectsEvents;

    // ...

    public function onStartingEventReplay()
    {
        Account::truncate();
    }
}
```

After all events are replayed, the `onFinishedEventReplay` method will be called, should your projector have one.

## Models with timestamps

When using models with timestamps, it is important to keep in mind that the projector will create or update these models when replaying and the timestamps will not correspond to the event's original timestamps. This will probably not be behaviour you intended. To work around this you can use the stored event's timestamps:

```php
public function onAccountCreated(StoredEvent $storedEvent, AccountCreated $event) {
        Account::create(array_merge($event->accountAttributes, ['created_at' => $storedEvent->created_at, 'updated_at' => $storedEvent->created_at]));
}
```

## What about reactors?

Reactors are used to handle side effects, like sending mails and such. You'll only want reactors to do their work when an event is originally fired. You don't want to send out mails when replaying events. That's why reactors will never get called when replaying events.
