---
title: Commands
weight: 13
---

Our package supports a simple command bus implementation that's able to automatically map commands to aggregate roots. If you want to use dedicated command objects, this can be a useful feature in order to prevent much boilerplate for writing command handlers. It's also possible to manually create command handlers if you want to.

A command looks like this:

```php
namespace Spatie\Shop\Cart\Commands;

use Spatie\Shop\Support\EventSourcing\Attributes\AggregateUuid;
use Spatie\Shop\Support\EventSourcing\Attributes\HandledBy;

#[HandledBy(CartAggregateRoot::class)]
class AddCartItem
{
    public function __construct(
        #[AggregateUuid] public string $cartUuid,
        public string $cartItemUuid,
        public Product $product,
        public int $amount,
    ) {
    }
}
```

The `#[HandledBy]` attribute takes any invokable class. If you use an aggregate root class, the `#[AggregateUuid]` property is also required in order to map a command to an aggregate root.

If a command is handled by an aggregate root, you can simply add a method on that aggregate root which takes the command as an argument, and the rest will be handled for you:

```php
class CartAggregateRoot extends AggregateRoot
{
    // …

    public function addItem(
        AddCartItem $addCartItem
    ): self {
        // …
    }
}
```

Also note that these commands can be mapped to aggregate partials, this is especially useful when you don't want to provide a manual mapping of public methods between the aggregate root and its partials.

```php
class CartItems extends AggregatePartial
{
    // …

    public function addItem(
        AddCartItem $addCartItem
    ): self {
        // …
    }
}
```

Finally, a command is dispatched on the `CommandBus`, which is registered in Laravel's container, so you can inject is wherever needed:

```php
class CartController
{
    public function addCartItem(
        Request $request,
        CommandBus $commandBus
    ): void {
        $commandBus->dispatch(new AddCartItem(
            cartUuid: /* … */,
            cartItemUuid: /* … */,
            product: /* … */,
            amount: /* … */,
        ));
    }
}
```

## Want to know more?

Commands can be a very useful tool in complex applications. Our course, [Event Sourcing in Laravel](https://event-sourcing-laravel.com/) covers them in depth:

- 13. The Command Bus
- 14. CQRS
