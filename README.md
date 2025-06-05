<div align="left">
    <a href="https://spatie.be/open-source?utm_source=github&utm_medium=banner&utm_campaign=laravel-event-sourcing">
      <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://spatie.be/packages/header/laravel-event-sourcing/html/dark.webp">
        <img alt="Logo for laravel-event-sourcing" src="https://spatie.be/packages/header/laravel-event-sourcing/html/light.webp">
      </picture>
    </a>

<h1>Event sourcing for Artisans 📽</h1>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-event-sourcing.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-event-sourcing)
![Tests](https://github.com/spatie/laravel-event-sourcing/workflows/run-tests/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-event-sourcing.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-event-sourcing)
    
</div>

This package aims to be the entry point to get started with event sourcing in Laravel. It can help you with setting up aggregates, projectors, and reactors. 

If you've never worked with event sourcing, or are uncertain about what aggregates, projectors and reactors are head over to the getting familiar with event sourcing section [in our docs](https://spatie.be/docs/laravel-event-sourcing/v7/introduction).

Event sourcing might be a good choice for your project if:

- your app needs to make decisions based on the past
- your app has auditing requirements: the reason why your app is in a certain state is equally as important as the state itself
- you foresee that there will be a reporting need in the future, but you don't know yet which data you need to collect for those reports

If you want to skip to reading code immediately, here are some example apps. In each of them, you can create accounts and deposit or withdraw money. 

- [Larabank built traditionally without event sourcing](https://github.com/spatie/larabank-traditional)
- [Larabank built with projectors](https://github.com/spatie/larabank-event-projector)
- [Larabank built with aggregates and projectors](https://github.com/spatie/larabank-event-projector-aggregates)

## Event sourcing in Laravel course

If you want to learn more about event sourcing, check out [our course on event sourcing in Laravel](https://event-sourcing-laravel.com)

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-event-sourcing.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-event-sourcing)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Documentation

You can find installation instructions and detailed instructions on how to use this package at [the dedicated documentation site](https://spatie.be/docs/laravel-event-sourcing/v7/introduction/).

## Upgrading from laravel-event-projector

This package supercedes [laravel-event-projector](https://github.com/spatie/laravel-event-projector). It has the same API. Upgrading from laravel-event-projector to laravel-event-sourcing is easy. Take a look at [our upgrade guide](UPGRADING.md).

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you've found a bug regarding security please mail [security@spatie.be](mailto:security@spatie.be) instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Kruikstraat 22, 2018 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

The aggregate root functionality is heavily inspired by [Frank De Jonge](https://twitter.com/frankdejonge)'s excellent [EventSauce](https://eventsauce.io/) package. A big thank you to [Dries Vints](https://github.com/driesvints) for giving lots of valuable feedback while we were developing the package. 

## Footnotes

<a name="footnote1"><sup>1</sup></a> Quote taken from [Event Sourcing made Simple](https://kickstarter.engineering/event-sourcing-made-simple-4a2625113224)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
