---
title: Testing aggregates
weight: 3
---

In the test suite of your application you probably also want to write some tests to check if an aggregate works correctly. The package contains some handy methods to help you.

Imagine you have an `AccountAggregateRoot` that handles adding and subtract an amount for a bank account. The account has a limit of -$5000.

```php
use Spatie\EventSourcing\AggregateRoot;

class AccountAggregateRoot extends AggregateRoot
{
    /** @var int */
    private $balance = 0;

    /** @var int */
    private $accountLimit = -5000;

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

    protected function applyMoneyAdded(MoneyAdded $event)
    {
        $this->balance += $event->amount;
    }

    public function subtractMoney(int $amount)
    {
        $this->hasSufficientFundsToSubtractAmount($amount)
            ? $this->recordThat(new MoneySubtracted($amount))
            : $this->recordThat(new AccountLimitHit($amount));
    }

    protected function applyMoneySubtracted(MoneySubtracted $event)
    {
        $this->balance -= $event->amount;
    }

    private function hasSufficientFundsToSubtractAmount(int $amount): bool
    {
        return $this->balance - $amount >= $this->accountLimit;
    }
}
```

Let's now test that rule that an account cannot go beyond its limit. 

```php
// in a PHPUnit test

/** @test */
public function it_can_subtract_money()
{
    AccountAggregateRoot::fake()
        ->given(new SubtractMoney(4999))
        ->when(function (AccountAggregate $accountAggregate) {
            $accountAggregate->subtractMoney(1);
        })
        ->assertRecorded(new MoneySubtracted(1))
        ->assertNotRecorded(AccountLimitHit::class);
}

/** @test */
public function it_will_not_make_subtractions_that_would_go_below_the_account_limit()
{
    AccountAggregateRoot::fake()
        ->given(new SubtractMoney(4999))
        ->when(function (AccountAggregate $accountAggregate) {
            $accountAggregate->subtractMoney(2);
        })
        ->assertRecorded(new AccountLimitHit(2))
        ->assertNotRecorded(MoneySubtracted::class);
}
```

You could write the above test a bit shorter. The given events can be passed to the `fake` method. You're also not required to use the `when` function.

```php
/** @test */
public function it_will_not_make_subtractions_that_would_go_below_the_account_limit()
{
    AccountAggregateRoot::fake(new SubtractMoney(4999))
        ->subtractMoney(2)
        ->assertRecorded(new AccountLimitHit(2))
        ->assertNotRecorded(MoneySubtracted::class);
}
```

The `fake`, `given` and `assertRecorded` methods can accept a single event instances or an array with event instances. `assertNotRecorded` can also accept an array of class names.

If you don't expect any events to be recorded you can use `assertNothingRecorded`.

## Disabling dispatching events

When calling the `given` method the aggregate will fire of events for your projector and reactor to react to. If you don't want events being dispatched. Simply [use the `Event` facade's `fake` method](https://laravel.com/docs/master/mocking#event-fake) before your test executes.

```php
\Illuminate\Support\Facades\Event::fake();
``` 

If you would prefer to disable Projectors reacting to events whilst retaining `Event` functionality simply use the `Projectionist` facade's `withoutEventHandlers` method before your test executes.

```php
\Spatie\EventSourcing\Facades\Projectionist::withoutEventHandlers();
```
