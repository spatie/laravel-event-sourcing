---
title: Handling exceptions
weight: 4
---

The `event-sourcing` config file has a key, `catch_exceptions`, that determines what will happen should a projector or reactor throw an exception. If this setting is set to `false`, exceptions will not be caught and your app will come to a grinding halt.

If `catch_exceptions` is set to `true`, and a projector or reactor throws an exception, all other projectors and reactors will still get called. The `Projectionist` will catch all exceptions and fire the `EventHandlerFailedHandlingEvent`. That event contains these public properties:

- `eventHandler`: The projector or reactor that could not handle the event.
- `storedEvent`: The instance of `Spatie\EventSourcing\Models\StoredEvent` that could not be handled.
- `exception`: The exception thrown by the `EventHandler`.

It will also call the `handleException` method on the projector or reactor that threw the exception. It will receive the thrown error as the first argument. If you throw an exception in `handleException`, the `Projectionist` will not catch it and your php process will fail.
