---
title: Using your own event serializer
weight: 8
---

Events will be serialized by the `Spatie\EventSourcing\EventSerializers\JsonEventSerializer`. Like the name implies, this class can serialize an event to json so it can be easily stored in a `json` column in the database.

You can specify your own serializer by creating a class that implements `Spatie\EventSourcing\EventSerializers\EventSerializer` and specifying the class in the `event_serializer` key of the `event-sourcing.php` config file.

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

- 16. Event Versioning
