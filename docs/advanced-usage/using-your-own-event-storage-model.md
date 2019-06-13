---
title: Using your own event storage model
weight: 5
---

The default model responsible for storing events is `\Spatie\EventProjector\Models\StoredEvent`. If you want to add behaviour to that model you can create a class of your own that extends the `StoredEvent` model. You should put the class name of your model in the `stored_event_model` in the `event-projector.php` config file.
