---
title: Introduction
weight: 1
---

This package aims to be the entry point to get started with event sourcing in Laravel. It can help you setting up aggregates, projectors and reactors. 

If you've never worked with event sourcing, or are uncertain about what projectors, reactors and aggregates are, head over to [the getting familiar with event sourcing section](https://docs.spatie.be/laravel-event-projector/v2/getting-familiar-with-event-sourcing/introduction).

Are you visual learner? Then start by watching this video. It explains event sourcing in general and how you can use projectors, reactors and aggregates.

<iframe width="560" height="315" src="https://www.youtube.com/embed/9tbxl_I1EGE" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Event sourcing might be a good choice for your project if:

- your app needs to make decisions based on the past
- your app has auditing requirements: the reason why your app is in a certain state is equally as important as the state itself
- you foresee that there will be a reporting need in the future, but you don't know yet which data you need to collect for those reports

## We have badges!

<section class="article_badges">
    <a href="https://github.com/spatie/laravel-event-projector/releases"><img src="https://img.shields.io/github/release/spatie/laravel-event-projector.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://github.com/spatie/laravel-event-projector/blob/master/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
    <a href="https://travis-ci.org/spatie/laravel-event-projector"><img src="https://img.shields.io/travis/spatie/laravel-event-projector/master.svg?style=flat-square" alt="Build Status"></a>
    <a href="https://scrutinizer-ci.com/g/spatie/laravel-event-projector"><img src="https://img.shields.io/scrutinizer/g/spatie/laravel-event-projector.svg?style=flat-square" alt="Quality Score"></a>
    <a href="https://packagist.org/packages/spatie/laravel-event-projector"><img src="https://img.shields.io/packagist/dt/spatie/laravel-event-projector.svg?style=flat-square" alt="Total Downloads"></a>
</section>
