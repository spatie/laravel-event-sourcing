---
title: Aggregate Partials
weight: 11
---

Aggregate partials can be used to split large aggregate roots into separate classes. An aggregate partial belongs to an aggregate root, so from the outside you'd still interact with the aggregate root.

Just like aggregate roots, aggregate partials can record and apply events, these events are linked to the aggregate root a partial belongs to.

Here's an example of an aggregate partial, which manages cart items and is part of the cart aggregate root:

```php
namespace Spatie\Shop\Cart\Partials;

use Spatie\EventSourcing\AggregateRoots\AggregatePartial;

class CartItems extends AggregatePartial
{
    private array $cartItems = [];

    public function isEmpty(): bool
    {
        return count($this->cartItems) === 0;
    }
    
    public function addItem(
        string $cartItemUuid,
        Product $product,
        int $amount
    ): self {
        $this->recordThat(new CartItemAdded(
            cartItemUuid: $cartItemUuid,
            productUuid: $product->uuid,
            amount: $amount,
        ));

        return $this;
    }

    protected function applyCartItemAdded(
        CartItemAdded $cartItemAdded
    ): void {
        $this->cartItems[$cartItemAdded->cartItemUuid] = null;
    }
}
```

The cart aggregate root, in its turn, links to its partials by adding them as protected properties:

```php
class CartAggregateRoot extends AggregateRoot
{
    protected CartItems $cartItems;

    public function __construct()
    {
        $this->cartItems = new CartItems($this);
    }
}
```

The package will determine automatically that `CartItems` is a partial, and will dispatch events to it without any other configuration.

As said before, you'd still interact with the aggregate root from the outside, so `CartAggregateRoot` still needs a method to add an item, though all functionality related to it is moved to a separate class:

```php
class CartAggregateRoot extends AggregateRoot
{
    // …
    
    public function addItem(
        string $cartItemUuid,
        Product $product,
        int $amount
    ): self {
        $this->cartItems->addItem(
            $cartItemUuid,
            $product,
            $amount,
        );

        return $this;
    }
}
```

## Want to know more?

Our course, [Event Sourcing in Laravel](https://event-sourcing-laravel.com/) covers aggregate roots and partials in depth:

- 9. Aggregate Roots
- 10. State Management in Aggregate Roots
- 11. Aggregate Partials
- 12. State Machines with Aggregate Partials
