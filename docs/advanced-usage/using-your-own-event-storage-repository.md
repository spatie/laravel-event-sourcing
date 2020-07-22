---
title: Using your own event storage repository
weight: 7
---

The default repository responsible for storing events is `\Spatie\EventSourcing\StoredEvents\Repositories\EloquentStoredEventRepository`. If you want to use a different storage method, implement the `\Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository` repository and add your custom functionality.
