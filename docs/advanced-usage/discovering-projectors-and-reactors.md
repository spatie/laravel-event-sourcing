---
title: Discovering projectors and reactors
weight: 5
---

By default the package will automatically discover all projectors and reactors and will register them at the projectionist.

If you want to see a list of the discovered projectors and reactors perform the `event-sourcing:list` Artisan command. Here's how the output could look like:

<img src="/docs/laravel-event-sourcing/v7/images/list.png" />

## Caching discovered projectors and reactors

In production, you likely do not want the package to scan all of your classes on every request. Therefore, during your deployment process, you should run the `event-sourcing:cache-event-handlers` Artisan command to cache a manifest of all of your application's projectors and reactors. This manifest will be used by the package to speed up the registration process. The `event-sourcing:clear-event-handlers` command may be used to remove the manifest.

## Disabling discovery

If you want to turn off auto-discovery to enforce manually registration of projectors and reactors, just set the `auto_discover_projectors_and_reactors` key in the `event-sourcing` config file to an empty array.
