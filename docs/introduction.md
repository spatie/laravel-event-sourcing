---
title: Introduction
weight: 1
---

This package aims to be the entry point to get started with event sourcing in Laravel. It can help you setting up aggregates, projectors and reactors.

If you've never worked with event sourcing, or are uncertain about what projectors, reactors and aggregates are, head over to [the getting familiar with event sourcing section](/laravel-event-sourcing/v4/getting-familiar-with-event-sourcing/introduction).

Event sourcing might be a good choice for your project if:

- your app needs to make decisions based on the past
- your app has auditing requirements: the reason why your app is in a certain state is equally as important as the state itself
- you foresee that there will be a reporting need in the future, but you don't know yet which data you need to collect for those reports

Some concepts in the package, for example the testing methods of aggregates, were inspired by [Frank De Jonge](https://twitter.com/frankdejonge/)'s [EventSauce](https://eventsauce.io/) package.

## A premium course on event sourcing

Our team is currently developing [a premium course on event sourcing](https://spatie.be/event-sourcing).

In this course, we'll walk you through all the basics. Though the knowledge presented is framework agnostic, the examples will embrace Laravel.
The course will include a cart package that will be event sourced and can be used in your e-commerce projects.

Subscribe to [our mailing list at spatie.be](https://spatie.be/event-sourcing) now to be notified when we launch it!

## We have badges!

<section class="article_badges">
    <a href="https://github.com/spatie/laravel-event-sourcing/releases"><img src="https://img.shields.io/github/release/spatie/laravel-event-sourcing.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://github.com/spatie/laravel-event-sourcing/blob/master/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
    <a href="https://packagist.org/packages/spatie/laravel-event-sourcing"><img src="https://img.shields.io/packagist/dt/spatie/laravel-event-sourcing.svg?style=flat-square" alt="Total Downloads"></a>
</section>
