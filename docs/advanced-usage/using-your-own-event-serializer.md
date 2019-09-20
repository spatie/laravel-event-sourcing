---
title: Using your own event serializer
weight: 8
---

Events will be serialized by the `Spatie\EventSourcing\EventSerializers\JsonEventSerializer`. Like the name implies, this class can serialize an event to json so it can be easily stored in a `json` column in the database.

You can specify your own serializer by creating a class that implements `Spatie\EventSourcing\EventSerializers\EventSerializer` and specifying the class in the `event_serializer` key of the `event-sourcing.php` config file.

This is the content of the `EventSerializer` interface:

```
namespace Spatie\EventSourcing\EventSerializers;

use Spatie\EventSourcing\ShouldBeStored;

interface EventSerializer
{
    public function serialize(ShouldBeStored $event): string;

    public function deserialize(string $eventClass, string $json): ShouldBeStored;
}
```
