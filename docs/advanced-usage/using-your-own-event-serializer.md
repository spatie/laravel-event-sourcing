---
title: Using your own event serializer
weight: 6
---

Events will be serialized by the `Spatie\EventProjector\EventSerializers\JsonEventSerializer`. Like the name implies, this class can serialize an event to json so it can be easily stored in a `json` column in the database.

You can specify your own serializer by creating a class that implements `Spatie\EventProjector\EventSerializers\EventSerializer` and specifying the class in the `event_serializer` key of the `event-projector.php` config file.

This is the content of the `EventSerializer` interface:

```
namespace Spatie\EventProjector\EventSerializers;

use Spatie\EventProjector\ShouldBeStored;

interface EventSerializer
{
    public function serialize(ShouldBeStored $event): string;

    public function deserialize(string $eventClass, string $json): ShouldBeStored;
}
```
