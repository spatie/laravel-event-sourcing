---
title: Thinking in events
weight: 5
---

In this example we're going to try to send a mail whenever an account is broke (balance below zero). You can do this with projectors and reactors alone, but aggregates might be a better fit for this. Aggregates make it easy to make decisions based on past events. Check out the section on [how to use aggregates](/docs/laravel-event-sourcing/v7/using-aggregates/writing-your-first-aggregate) to learn more about them, or keep reading on this page if you don't want to use aggregates.

Let's build upon the examples shown in the [writing your first projector](/docs/laravel-event-sourcing/v7/using-projectors/writing-your-first-projector) and [handling side effects with reactors](/docs/laravel-event-sourcing/v7/using-reactors/writing-your-first-reactor)' sections.

Imagine you are tasked with sending a mail to an account holder whenever he or she is broke. You might think, that's easy, let's just check in a new reactor if the account balance is less than zero.

Let's first add a little helper method to the `Account` model to check if an account is broke.

```php
// ...

class Account extends Projection
{
    // ...

    public function isBroke(): bool
    {
        return $this->balance < 0;
    }
}
```

Now create a new reactor called `BrokeReactor`:

```php
namespace App\Reactors;

// ...

class BrokeReactor implements EventHandler
{
    use HandlesEvents;

    public function onMoneySubtracted(MoneySubtracted $event)
    {
        $account = Account::uuid($event->accountUuid);

        if ($account->isBroke()) {
            Mail::to($account->email)->send(new BrokeMail($account));

            event(new BrokeMailSent($account->uuid));
        }
    }
}
```

A mail will get sent when an account is broke. The problem with this approach is that mails will also get sent for accounts that were already broke before. If you want to only sent mail when an account went from a positive balance to a negative balance we need to do some more work.

You might be tempted to add some kind of flag here that determines if the mail was already sent.

But you should never let reactors write to models (or whatever storage mechanism you use) you've built up using projectors. If you were to do that, all changes would get lost when replaying events: events won't get passed to reactors when replaying them. Keep in mind that reactors are meant for side effects, not for building up state.

If you are tempted to modify state in a reactor, just fire off a new event and let a projector modify the state. Let's modify the `BrokeReactor` to do just that. If you're following along don't forget to create a migration that adds the `broke_mail_sent` field to the `accounts` table.

```php
// ...

class BrokeReactor implements EventHandler
{
    use HandlesEvents;

    public function onMoneySubtracted(MoneySubtracted $event)
    {
        $account = Account::uuid($event->accountUuid);

        /*
         * Don't send a mail if an account isn't broke
         */
        if (! $account->isBroke()) {
            return;
        }

        /*
         * Don't send a mail if it was sent already
         */
        if ($account->broke_mail_sent) {
            return;
        }

        Mail::to($account->email)->send(new BrokeMail($account));

        /*
         * Send out an event so the projector can modify the state.
         */
        event(new BrokeMailSent($account->uuid));
    }
}
```

Let's leverage that new event in the `AccountBalanceProjector`.

```php
// ...

class AccountBalanceProjector extends Projector
{
    public function onBrokeMailSent(BrokeMailSent $event)
    {
        $account = Account::uuid($event->accountUuid);

        $account->broke_mail_sent = true;

        $account->writeable()->save();
    }

    public function onMoneyAdded(MoneyAdded $event)
    {
        $account = Account::uuid($event->accountUuid);

        $account->balance += $event->amount;

        /*
         * If the balance is above zero again, set the broke_mail_sent
         * flag to false again, so we can send another mail
         * when the balance goes below zero again.
         */
        if ($account->balance >= 0) {
            $account->broke_mail_sent = false;
        }

        $account->writeable()->save();
    }
}
```

The `BrokeReactor` will only send out a mail when an account goes broke. No mails will be sent if the account was already broke. When the account goes above zero and goes broke again a new mail will be sent.  When replaying all events, no mail will get sent, but all account state will be correct.

## Want to know more?

We discuss projections and complex patterns such as CQRS in depth in our [Event Sourcing in Laravel](https://event-sourcing-laravel.com/) course.
