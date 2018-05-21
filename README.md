**THIS PACKAGE IS STILL IN DEVELOPMENT**

# Simple event sourcing in Laravel 📽

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-event-projector.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-event-projector)
[![Build Status](https://img.shields.io/travis/spatie/laravel-event-projector/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-event-projector)
[![StyleCI](https://styleci.io/repos/133496112/shield?branch=master)](https://styleci.io/repos/133496112)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-event-projector.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-event-projector)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-event-projector.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-event-projector)

Event sourcing is to data what Git is to code <sup>[1](#footnote1)</sup>. Most applications have their current state stored in a database. By storing only the current state a lot of information is lost. You don't know how the application got in this state.

Event sourcing tries to solve that problem by saving all events that happen in your app. The state of your application is built by listening to those events. 

Here's a traditional example to make it more clear. Imagine you're a bank. Your clients have accounts. Instead of storing the balance of the accounts, you could store all the transactions. That way you not only know the balance of the account but also the reason why it's that specific number. There are many other benefits of storing events.

This package aims to be the simple and very pragmatic way to get started with event sourcing in Laravel.

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-event-projector
```

Next up you should publish the migration by running

```
php artisan vendor:publish --provider="Spatie\EventProjector\EventProjectorServiceProvider" --tag="migrations"
```

After the migration has been published you can create the `stored_events` table by running the migrations:

```
php artisan migrate
```

Optionally you may publish the config file. It will be published at `config/event-projector.php`. This is the default content:

```php
// coming soon
```


Coming soon...

## Usage

Coming soon...

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Resources

-  [Event Sourcing made Simple](https://kickstarter.engineering/event-sourcing-made-simple-4a2625113224): the blogpost that showed us that event sourcing can be used very pragmatically as well
- [EventSauce](https://eventsauce.io/): A pragmatic, feature rich event sourcing library for PHP made by [Frank de Jonge](https://frankdejonge.nl)
- [prooph](https://github.com/prooph): A full blown CQRS and event sourcing solution in PHP
- [The Many Meanings of Event-Driven Architecture](https://www.youtube.com/watch?v=STKCRSUsyP0): A recording of a cool talk by [Martin Fowler](https://martinfowler.com/)

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## Footnotes

<a name="footnote1"><sup>1</sup></a> Quote taken from [Event Sourcing made Simple](https://kickstarter.engineering/event-sourcing-made-simple-4a2625113224)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
