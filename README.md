# Event sourcing for Artisans 📽

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-event-sourcing.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-event-sourcing)
[![Build Status](https://img.shields.io/travis/spatie/laravel-event-sourcing/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-event-sourcing)
[![StyleCI](https://styleci.io/repos/133496112/shield?branch=master)](https://styleci.io/repos/133496112)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-event-sourcing.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-event-sourcing)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-event-sourcing.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-event-sourcing)

This package aims to be the entry point to get started with event sourcing in Laravel. It can help you with setting up aggregates, projectors, and reactors. 

If you've never worked with event sourcing, or are uncertain about what aggregates, projectors and reactors are head over to the getting familiar with event sourcing section [in our docs](https://docs.spatie.be/laravel-event-sourcing/v3/getting-familiar-with-event-sourcing/introduction).

Event sourcing might be a good choice for your project if:

- your app needs to make decisions based on the past
- your app has auditing requirements: the reason why your app is in a certain state is equally as important as the state itself
- you foresee that there will be a reporting need in the future, but you don't know yet which data you need to collect for those reports

If you want to skip to reading code immediately, here are some example apps. In each of them, you can create accounts and deposit or withdraw money. 

- [Larabank built traditionally without event sourcing](https://github.com/spatie/larabank-traditional)
- [Larabank built with projectors](https://github.com/spatie/larabank-event-sourcing)
- [Larabank built with aggregates and projectors](https://github.com/spatie/larabank-event-sourcing-aggregates)

## Documentation

You can find installation instructions and detailed instructions on how to use this package at [the dedicated documentation site](https://docs.spatie.be/laravel-event-sourcing).

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

The aggregate root functionality is heavily inspired by [Frank De Jonge](https://twitter.com/frankdejonge)'s excellent [EventSauce](https://eventsauce.io/) package. A big thank you to [Dries Vints](https://github.com/driesvints) for giving lots of valuable feedback while we were developing the package. 

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## Footnotes

<a name="footnote1"><sup>1</sup></a> Quote taken from [Event Sourcing made Simple](https://kickstarter.engineering/event-sourcing-made-simple-4a2625113224)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
