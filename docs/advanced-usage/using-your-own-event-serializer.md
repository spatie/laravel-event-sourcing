---
title: Using your own event serializer
weight: 8
---

Events will be serialized by the `Spatie\EventSourcing\EventSerializers\JsonEventSerializer`. Like the name implies, this class can serialize an event to json so it can be easily stored in a `json` column in the database.

You can specify your own serializer by creating a class that implements `Spatie\EventSourcing\EventSerializers\EventSerializer` and specifying the class in the `event_serializer` key of the `event-sourcing.php` config file.

The default `JsonEventSerializer` is registered as a singleton, so the same instance and its normalizers are reused for every event resolved through the `EventSerializer` interface. Custom normalizers must not retain event-specific state between calls. In long-running workers, this instance lives for the lifetime of the application.

By default, custom serializers configured via `event_serializer` retain their transient binding: a new instance is created each time the interface is resolved. Serializers selected via an event's `#[EventSerializer]` attribute are resolved separately and are not made singletons by this binding.

This is the content of the `EventSerializer` interface:

```php
namespace Spatie\EventSourcing\EventSerializers;

use Spatie\EventSourcing\ShouldBeStored;

interface EventSerializer
{
    public function serialize(ShouldBeStored $event): string;

    public function deserialize(string $eventClass, string $json, int $version, ?string $metadata = null): ShouldBeStored;
}
```

## Reusing a custom serializer

If your custom serializer and its normalizers keep no event-specific state, you can opt into singleton reuse too. On Laravel versions that support the [`#[Singleton]` attribute](https://laravel.com/docs/container#singleton-attribute), add it to your serializer class:

```php
namespace App\EventSerializers;

use Illuminate\Container\Attributes\Singleton;
use Spatie\EventSourcing\EventSerializers\JsonEventSerializer;

#[Singleton]
class CustomEventSerializer extends JsonEventSerializer
{
}
```

Set `event_serializer` to `App\EventSerializers\CustomEventSerializer::class`. Laravel will reuse the same custom serializer instance when the package resolves the `EventSerializer` interface. The attribute also applies when the class is resolved directly, such as through an event's `#[EventSerializer]` attribute.

Alternatively, including on Laravel versions without the attribute, register the interface as a singleton in your application's service provider:

```php
use App\EventSerializers\CustomEventSerializer;
use Spatie\EventSourcing\EventSerializers\EventSerializer;

$this->app->singleton(EventSerializer::class, CustomEventSerializer::class);
```

As with the default serializer, a singleton custom serializer and its normalizers live for the lifetime of the application, including across jobs in long-running workers.

## Upgrading Events

If an event payload has changed overtime, old events can be "upgraded" to the new payload on the fly
in the event serializer. 

Using our larabank example, let's imagine that we've gone international and our new accepting 
international payments. Our `MoneyAdded` events will need to have an additional field for
the currency.

```php
use App\Events\MoneyAdded;

class UpgradeSerializer extends JsonEventSerializer
{
    public function deserialize(string $eventClass, string $json, int $version, ?string $metadata = null): ShouldBeStored
    {
        $event = parent::deserialize($eventClass, $json, $version, $metadata);

        // all currency was USD before we started accepting other currencies
        if ($eventClass === MoneyAdded::class && empty($event->currency)) {
            $event->currency = 'USD';
        }

        return $event;
    }
}
```

## Want to know more?

Our course, [Event Sourcing in Laravel](https://event-sourcing-laravel.com/) covers this topic in depth:

- 18\. Event Versioning
