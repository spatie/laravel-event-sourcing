---
title: Discovering projectors and reactors
weight: 4
---

By default the package will automatically discover all projectors and reactors and will register them at the projectionist. 

If you want to see a list of the discovered projectors and reactors perform the `event-projector:list` Artisan command. Here's how the output could look like:

<img src="../../images/list.png" />

## Caching discovered projectors and reactors

In production, you likely do not want the package to scan all of your classes on every request. Therefore, during your deployment process, you should run the `event-projector:cache-event-handlers` Artisan command to cache a manifest of all of your application's projectors and reactors. This manifest will be used by the package to speed up the registration process. The `event-projector:clear-event-handlers` command may be used to destroy the cache.

## Disabling disovery

If you want to turn off autodiscovery and want to enforce manualy registration of projectors and reactors, just set the `auto_discover_projectors_and_reactors` key in the `event-projector` config file to an empty array.
