---
title: Using your own event storage repository
weight: 7
---

The default repository responsible for storing events is `\Spatie\EventProjector\EloquentStoredEventRepository`. If you want to use a different storage method, implement the `\Spatie\EventProjector\StoredEventRepository` repository and add your custom functionality.
