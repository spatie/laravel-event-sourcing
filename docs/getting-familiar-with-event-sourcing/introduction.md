---
title: Introduction
weight: 1
---

Event sourcing is to data what Git is to code. Most applications only have their current state stored in a database. A lot of useful information gets lost: you don't know _how_ the application got to this state.

Event sourcing tries to solve this problem by storing all events that happen in your app. The state of your application is built by listening to those events. An aggregate is used to validate if a new event is allowed to get written and to make decisions based on the past. Projectors are used to transform newly written events into a format useful for consumption in your app.

Here's a concrete example to make it more clear. Imagine you're a bank. Your clients have accounts. Storing the balance of the accounts wouldn't be enough; all the transactions should be remembered too. With event sourcing, the balance isn't a standalone database field, but a value calculated from the stored transactions.

After taking a look at [an example of traditional application](/docs/laravel-event-sourcing/v7/getting-familiar-with-event-sourcing/the-traditional-application), we're going to discuss the two concepts that make up this package: [projectors](/docs/laravel-event-sourcing/v7/getting-familiar-with-event-sourcing/using-projectors-to-transform-events) and [aggregates](/docs/laravel-event-sourcing/v7/getting-familiar-with-event-sourcing/using-aggregates-to-make-decisions-based-on-the-past).

If you want to skip to reading code immediately, here are the Larabank example apps used in this section. In all of them, you can create accounts and deposit or withdraw money.

- [Larabank built traditionally without event sourcing](https://github.com/spatie/larabank-traditional)
- [Larabank built with projectors](https://github.com/spatie/larabank-projectors)
- [Larabank built with aggregates and projectors](https://github.com/spatie/larabank-aggregates)

## Want to know more?

In our course [Event Sourcing in Laravel](https://event-sourcing-laravel.com/), we discuss the event-driven mindset in depth, and also learn how an event bus works, how event are stored and how to model an event-driven system.
