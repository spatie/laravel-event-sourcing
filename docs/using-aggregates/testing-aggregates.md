---
title: Testing aggregates
weight: 3
---

In the test suite of your application you probably also want to write some tests to check if an aggregate works correctly. The package contains some handy methods to help you.

Let's test [the business rule](https://docs.spatie.be/laravel-event-projector/v2/using-aggregates/writing-your-first-aggregate/#implementing-our-first-business-rule) from the previous section. An account cannot go below -$5000. When the limit has been hit three times a `LoanProposedEvent` should be recorded.

```php
AccountAggregate::fake()
    ->given([
       new SubstractAmount(4500)
       new AccountLimitHit(1000),
       new AccountLimitHit(1000),
    ])
    ->when(function(AccountAggregate $accountAggregate) {
        $accountAggregate->subtractAmount(1234)
    })
    ->assertRecorded([
       new AccountLimitHit(1234),
       new LoanProposed(), 
    ])
    ->assertNotRecorded(MoneySubstracted::class);
```

You could write the above test a bit shorter. The given events can be passed to the `fake` method as well. You're also not required to use the `when` function.

```php
$accountAggregate = AccountAggregate::fake([
    new SubstractAmount(4500)
    new AccountLimitHit(1000),
    new AccountLimitHit(1000),
]);

$accountAggregate->subtractAmount(1234);

$accountAggregate->assertRecorded([
    new AccountLimitHit(1234),
    new LoanProposed(), 
]);
```

If you don't expect any events to be recorded you can use `assertNothingRecorded`.

