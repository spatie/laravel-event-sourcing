---
title: Preparing events
weight: 1
---

The package will listen for events that implement the `\Spatie\EventProjector\ShouldBeStored` interface. This is an empty interface that simply signals to the package that the event should be stored.

You can quickly create an event that implements `ShouldBeStored` by running this artisan command:

```bash
php artisan make:storable-event NameOfYourEvent
```

Here's an example of such event:

```php
namespace App\Events;

use Spatie\EventProjector\ShouldBeStored;

class MoneyAdded implements ShouldBeStored
{
    /** @var string */
    public $accountUuid;

    /** @var int */
    public $amount;

    public function __construct(string $accountUuid, int $amount)
    {
        $this->accountUuid = $accountUuid;

        $this->amount = $amount;
    }
}
```

Whenever an event that implements `ShouldBeStored` is fired it will be serialized and written in the `stored_events` table. Immediately after that, the event will be passed to all projectors and reactors.

If your event has an eloquent model, it should also use the `Illuminate\Queue\SerializesModels` trait so we are able to serialize these models correctly.

## Specifying a queue

When a `StoredEvent` is created, we'll dispatch a job on the queue defined in the `queue` key of the `event-projector` config file. Queued projectors and reactors will get called when the job is executed on the queue. 

On an event you can override the queue that should be used by adding a `queue` property.

```php
namespace App\Events;

use Spatie\EventProjector\ShouldBeStored;

class MyEvent implements ShouldBeStored
{
    public $queue = 'alternativeQueue';

    ...
}
```
