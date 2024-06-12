---
title: Using aggregates to make decisions based on the past
weight: 4
---

Now that you know what projections are, let's take it one step further with aggregates. In the previous examples whenever we wanted to fire off an event, we simply did so. When using aggregates, our main code is not going to fire events anymore. Instead, an aggregate will do that. An aggregate is a class that helps you to make decisions based on events that happened in the past.

Before firing off an event, an aggregate will first check if it is allowed to fire off that particular event. Using our Larabank example again, imagine you have to implement the rule that an account's balance is not allowed to go below -$5000. When trying to subtract money for a particular account, the aggregate will first loop through all previous events of that account and calculate the current balance. If the balance minus the amount we subtract is not less than -$5000, it will record that `MoneySubtracted` event. After that, the `MoneySubtracted` event will be passed to all projectors and reactors.

Let's go through this step by step.

Step 1: our app wants to subtract $1000. We create a new aggregate root instance and will feed it all events. There are no events yet to retrieve in this pass. The aggregate will conclude that it's allowed to subtract $1000 and will record that `Subtract` event. This recording is just in memory, and nothing will be written to the DB yet.

<figure class="scheme">
    <img class="scheme_figure" src="/docs/laravel-event-sourcing/v7/images/aggregate-01.svg">
</figure>

Step 2: We are going to persist the aggregate. When persisting an aggregate, all of the newly recorded events that aggregate will be written in the database. Also, if you have projectors set up, they will receive the newly persisted events as well.

<figure class="scheme">
    <img class="scheme_figure" src="/docs/laravel-event-sourcing/v7/images/aggregate-02.svg">
</figure>

Step 3: Let's hit that account limit and try to subtract $4800 now. First, the aggregate will be reconstituted from all previous events. Because it gets the earlier events it can calculate the current balance in memory (which is of course -$1000). The aggregate root can conclude that if we were to subtract $4800 we would cross our limit of -$5000. So it is not going to record that event. Instead, we could record that fact the account limit was hit.

<figure class="scheme">
    <img class="scheme_figure" src="/docs/laravel-event-sourcing/v7/images/aggregate-03.svg">
</figure>

Step 4: The aggregate gets persisted, and the account limit hit event gets written into the database.

<figure class="scheme">
    <img class="scheme_figure" src="/docs/laravel-event-sourcing/v7/images/aggregate-04.svg">
</figure>

So now we've protected our account from going below -\$5000. Let's take it one step further and send our customer a loan proposal mail when he or she hits the account limit three times in a row. Using an aggregate this is easy!

Step 5: Let's again try to subtract a lot of money to hit that account limit of \$5000. We hit our account limit the second time.

<figure class="scheme">
    <img class="scheme_figure" src="/docs/laravel-event-sourcing/v7/images/aggregate-05.svg">
</figure>

Step 6: This time it gets interesting. We are going to try to subtract money and will hit our limit for the third time. Our aggregate gets reconstituted from all events. Those events get fed to the aggregate one by one. The aggregate in memory holds a counter of how many limit hit events it receives. That counter is now on 2. Because the amount we subtract will take us over the account limit, the aggregate will not record a subtract event, but a new limit hit event. It will update the limit hit counter from 2 to 3. Because the counter is now at 3. It can also record a new event called loan proposed. When storing the aggregate, the new events will get persisted in the database. All projectors and reactor will get called with these events. The `LoanProposalReactor` hears that `LoanProposed` event and send the mail.

<figure class="scheme">
    <img class="scheme_figure" src="/docs/laravel-event-sourcing/v7/images/aggregate-06.svg">
</figure>

All of the above is a lot to wrap your mind around. To help you understand this better, here's our Larabank app again, but this time built [using aggregates](https://github.com/spatie/larabank-aggregates). In the controller, you see that we don't fire events, but we are [using an aggregate](https://github.com/spatie/larabank-aggregates/blob/cc9c85fb6569aa9259fe7f9bdd5ee23ec92b0c66/app/Http/Controllers/AccountsController.php#L21-L52). Inside the aggregate we are going to [record events](https://github.com/spatie/larabank-aggregates/blob/cc9c85fb6569aa9259fe7f9bdd5ee23ec92b0c66/app/Domain/Account/AccountAggregateRoot.php#L34) that will get written to the database as soon as we [persist](https://github.com/spatie/larabank-aggregates/blob/cc9c85fb6569aa9259fe7f9bdd5ee23ec92b0c66/app/Http/Controllers/AccountsController.php#L40) the aggregate.

Whenever we retrieve an aggregate, all of the previously stored events will be fed to the aggregate one by one to it's `apply*` methods. We can use [those apply methods](https://github.com/spatie/larabank-aggregates/blob/cc9c85fb6569aa9259fe7f9bdd5ee23ec92b0c66/app/Domain/Account/AccountAggregateRoot.php#L39-L46) to recalculate things like the balance, or the times the account limit was hit [as instance variables](https://github.com/spatie/larabank-aggregates/blob/cc9c85fb6569aa9259fe7f9bdd5ee23ec92b0c66/app/Domain/Account/AccountAggregateRoot.php#L79-L82). When we want to try to subtract money we can use those instances variables to decide whether we are going to [record the `MoneySubtracted` event or record other events](https://github.com/spatie/larabank-aggregates/blob/cc9c85fb6569aa9259fe7f9bdd5ee23ec92b0c66/app/Domain/Account/AccountAggregateRoot.php#L50-L62).

In summary, aggregates are used to make decisions based on past events.

If you want to know how to create and use aggregates, head over to [the `using-aggregates` section](/docs/laravel-event-sourcing/v7/using-aggregates/writing-your-first-aggregate).
