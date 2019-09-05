---
title: Using your own event storage repository
weight: 6
---

The default model responsible for storing events is `\Spatie\EventProjector\Models\EloquentStoredEvent`. If you want to add behaviour to that model you can create a class of your own that extends the `EloquentStoredEvent` model. You should put the class name of your model in the `stored_event_model` in the `event-projector.php` config file.
