---
title: Event Queries
weight: 12
---

Event queries are classes that represent in-memory projections. Whenever an event query is retrieved, it will query the relevant events from the database and apply them internally. These events may result in state that's exposed to the outside. 

Note that event queries should only ever be used to read data, and never result in changes.

Here's an example of an event query that will query order events to determine how much a collection of products have earned over a given period of time:

```php
namespace App\Reports;

use Illuminate\Support\Collection;
use Spatie\EventSourcing\EventHandlers\Projectors\EventQuery;
use Spatie\Period\Period;

class EarningsForProductAndPeriod extends EventQuery
{
    public function __construct(
        private Period $period,
        private Collection $products
    ) {
        // …
    }
}
```

The first step in implementing an event query is the determine which events should be applied, this is done by querying the events table, and applying every retrieved event. It's important to note that event queries are only a viable solution if you know you've got a limited data set to work with. For example: generating this report for a month or two should be fine, but if you're querying over a period of time that contains millions of events, you'll get in trouble.

Here's what the full constructor of our example query looks like:

```php
// ...
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

class EarningsForProductAndPeriod extends EventQuery
{
    public function __construct(
        private Period $period,
        private Collection $products
    ) {
        EloquentStoredEvent::query()
            // We're only interested in `OrderCreated` events
            ->whereEvent(OrderCreated::class)
            // And we only need events within a given period
            ->whereDate(
                'created_at', '>=', $this->period->getStart()
            )
            ->whereDate(
                'created_at', '<=', $this->period->getEnd()
            )
            ->each(
                fn (EloquentStoredEvent $event) => $this->apply($event->toStoredEvent())
            );
    }
}
```

The next step is to build internal state based on the given events:

```php
private int $totalPrice = 0;

// …

protected function applyOrderCreated(OrderCreated $orderCreated): void 
{
    $orderLines = collect(
        $orderCreated->orderData->orderLineData
    );

    $totalPriceForOrder = $orderLines
        // We're only interested in orders for a given product 
        ->filter(function (OrderLineData $orderLineData) {
            return $this->products->first(
                fn(Product $product) =>$orderLineData->productEquals($product)
            ) !== null;
        })
        // We the price of the relevant lines together
        ->sum(
            fn(OrderLineData $orderLineData) => $orderLineData->totalPriceIncludingVat
        );

    // Finally, we increment our total price, the grand total of all relevant events
    $this->totalPrice += $totalPriceForOrder;
}
```

Finally, the only things left to do are exposing the state, and using our query:

```php
class EarningsForProductAndPeriod extends EventQuery
{
    // …

    public function totalPrice(): int
    {
        return $this->totalPrice;
    }
}
```

```php
$report = new EarningsForProductAndPeriod(
    Period::make('2021-01-01', '2021-02-01'),
    $collectionOfProducts,
);

$report->totalPrice();
```

## Want to know more?

Our course, [Event Sourcing in Laravel](https://event-sourcing-laravel.com/) covers event queries in depth:

- 7. Event Queries
