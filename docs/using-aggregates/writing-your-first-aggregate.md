---
title: Writing your first aggregate
weight: 1
---

An aggregate is a class that decides to record events based on past events. To know more about their general purpose and the idea behind them, read this section on [using aggregates to make decisions-based-on-the-past](/docs/laravel-event-sourcing/v7/getting-familiar-with-event-sourcing/using-aggregates-to-make-decisions-based-on-the-past).

## Creating an aggregate

The easiest way to create an aggregate root would be to use the `make:aggregate` command:

```php
php artisan make:aggregate AccountAggregate
```

This will create a class like this:

```php
namespace App\Aggregates;

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;


class AccountAggregate extends AggregateRoot
{
}
```

## Recording events

You can add any methods or variables you need on the aggregate. To get you familiar with event modelling using aggregates let's implement a small piece of [the Larabank example app](https://github.com/spatie/larabank-aggregates). We are going to add methods to record the [`AccountCreated`](https://github.com/spatie/larabank-aggregates/blob/master/app/Domain/Account/Events/AccountCreated.php), [`MoneyAdded`](https://github.com/spatie/larabank-aggregates/blob/master/app/Domain/Account/Events/MoneyAdded.php) and the [`MoneySubtracted`](https://github.com/spatie/larabank-aggregates/blob/master/app/Domain/Account/Events/MoneySubtracted.php) events.

First, let's add a `createAccount` method to our aggregate that will record the `AccountCreated` event.

```php
namespace App\Aggregates;

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class AccountAggregate extends AggregateRoot
{
    public function createAccount(string $name, string $userId)
    {
        $this->recordThat(new AccountCreated($name, $userId));
        
        return $this;
    }

    public function addMoney(int $amount)
    {
        $this->recordThat(new MoneyAdded($amount));
        
        return $this;
    }

    public function subtractAmount(int $amount)
    {
        $this->recordThat(new MoneySubtracted($amount));
        
        return $this;
    }

    public function deleteAccount()
    {
        $this->recordThat(new AccountDeleted());
        
        return $this;
    }
}
```

The `recordThat` function will not persist the events to the database. It will simply hold them in memory. The events will get written to the database when the aggregate itself is persisted.

There are two things to notice. First, the method names are written in the present tense, not the past tense. We're trying to do something, and for the rest of our application it hasn't happened yet until the actual `AccountCreated` is saved. This will only happen when the `AccountAggregate` gets persisted.

The second thing to note is that nor the method and the event contain an uuid. The aggregate itself is aware of the uuid to use because it is passed to the retrieve method (`AccountAggregate::retrieve($uuid)`, we'll get to this in a bit). When persisting the aggregateroot, it will save the recorded events along with the uuid.

With this in place you can use the aggregate like this:

```php
AccountAggregate::retrieve($uuid)
    ->createAccount('my account', auth()->user()->id)
    ->persist();
```

```php
AccountAggregate::retrieve($uuid)
    ->addMoney(123)
    ->persist();
```

```php
AccountAggregate::retrieve($uuid)
    ->subtractMoney(456)
    ->persist();
```

When persisting an aggregate all newly recorded events inside aggregate root will be saved to the database. The newly recorded events will also get passed to all projectors and reactors that listen for them.

In our demo app we retrieve and persist the aggregate [in the `AccountsController`](https://github.com/spatie/larabank-aggregates/blob/c9f2ff240f4634ee2e241e3087ff60587a176ae0/app/Http/Controllers/AccountsController.php). The package has no opinion on where you should interact with aggregates. Do whatever you wish.

## Implementing our first business rule

Let's now implement the rule that an account cannot go below -$5000. Here's the thing to keep in mind: when retrieving an aggregate all events for the given uuid will be retrieved and will be passed to methods named `apply<className>` on the aggregate.

So for our aggregate to receive all past `MoneyAdded` and `MoneySubtracted` events we need to add `applyMoneyAdded` and`applyMoneySubtracted` methods to our aggregate. Because those events are all fed to the same instance of the aggregate, we can simply add an instance variable to hold the calculated balance.

```php
// in our aggregate

private $balance = 0;

//...

public function applyMoneyAdded(MoneyAdded $event)
{
    $this->balance += $event->amount;
}

public function applyMoneySubtracted(MoneySubtracted $event)
{
    $this->balance -= $event->amount;
}
```

Now that we have the balance of the account in memory, we can add a simple check to `subtractAmount` to prevents an event from being recorded.

```php
public function subtractAmount(int $amount)
{
    if (! $this->hasSufficientFundsToSubtractAmount($amount) {
        throw CouldNotSubtractMoney::notEnoughFunds($amount);
    }

    $this->recordThat(new MoneySubtracted($amount));
}

private function hasSufficientFundsToSubtractAmount(int $amount): bool
{
    return $this->balance - $amount >= $this->accountLimit;
}
```

## Implementing another business rule

We can take this one step further. You could also record the event that the account limit was hit.

```php
public function subtractAmount(int $amount)
{
    if (! $this->hasSufficientFundsToSubtractAmount($amount) {
        $this->recordThat(new AccountLimitHit($amount));

        // persist the aggregate so the record event gets persisted
        $this->persist();

        throw CouldNotSubtractMoney::notEnoughFunds($amount);
    }

    $this->recordThat(new MoneySubtracted($amount));
}
```

Let's now add a new business rule. Whenever somebody hits the limit three times a loan proposal should be sent. We can implement that as such.

```php
private $accountLimitHitCount = 0;

// we need to add this method to count the amount of this the limit was hit
public function applyAccountLimitHit()
{
    $this->accountLimitHitCount++;
}

public function subtractAmount(int $amount)
{
    if (! $this->hasSufficientFundsToSubtractAmount($amount) {
        $this->recordThat(new AccountLimitHit($amount));

        if ($this->accountLimitHitCount === 3) {
            $this->recordThat(new LoanProposed());
        }

        // persist the aggregate so the record events gets persisted
        $this->persist();

        throw CouldNotSubtractMoney::notEnoughFunds($amount);
    }

    $this->recordThat(new MoneySubtracted($amount));
}
```

When the limit is hit three times, we record another event `LoanProposed`. We could set up a reactor that listens for that event and sends the actual mail.

If you want to toy around with this example, clone the [Larabank with aggregates](https://github.com/spatie/larabank-aggregates) example.

## Want to know more?

Aggregate roots are a crucial part in large applications. Our course, [Event Sourcing in Laravel](https://event-sourcing-laravel.com/) covers them in depth:

- 09. Aggregate Roots
- 10. State Management in Aggregate Roots
- 11. Multi-Entity Aggregate Roots
- 12. State Machines with Aggregate Entities

