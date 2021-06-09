<?php

namespace Spatie\EventSourcing\Tests\AggregateRoots;

use Exception;
use Spatie\EventSourcing\AggregateRoots\AggregatePartial;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\Tests\TestCase;

class AggregatePartialTest extends TestCase
{
    protected const CART_UUID = 'cart-uuid';

    /** @test */
    public function test_entities()
    {
        $cart = Cart::retrieve(self::CART_UUID);

        $cart
            ->addItem('test')
            ->addItem('test 2')
            ->persist();

        $this->assertDatabaseCount((new EloquentStoredEvent)->getTable(), 2);

        $cart::retrieve(self::CART_UUID);

        $this->assertCount(2, $cart->cartItems->items);
        $this->assertEquals('test', $cart->cartItems->items[0]);
        $this->assertEquals('test 2', $cart->cartItems->items[1]);

        $cart->clear()->persist();

        $this->assertExceptionThrown(function () use ($cart) {
            $cart->checkout();
        });

        $cart::retrieve(self::CART_UUID);

        $this->assertExceptionThrown(function () use ($cart) {
            $cart->checkout();
        });
    }

    /** @test */
    public function test_partial_fakes()
    {
        $cartItems = CartItems::fake();

        $this->assertTrue($cartItems->isEmpty());

        $cartItems->addItem('test');

        $this->assertFalse($cartItems->isEmpty());
    }
}

class Cart extends AggregateRoot
{
    // Public for testing
    public CartItems $cartItems;

    public function __construct()
    {
        $this->cartItems = new CartItems($this);
    }

    public function checkout(): self
    {
        if ($this->cartItems->isEmpty()) {
            throw new Exception("Cart is empty");
        }

        // …

        return $this;
    }

    public function addItem(string $name): self
    {
        $this->cartItems->addItem($name);

        return $this;
    }

    public function clear(): self
    {
        $this->recordThat(new CartCleared);

        return $this;
    }

    public function applyClear(CartCleared $event): void
    {
        $this->cartItems = new CartItems($this);
    }
}

class CartItems extends AggregatePartial
{
    // Public for testing
    public array $items = [];

    public function addItem(string $name)
    {
        $this->recordThat(new ItemAdded($name));
    }

    public function applyAddItem(ItemAdded $itemAdded)
    {
        $this->items[] = $itemAdded->name;
    }

    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }
}

class ItemAdded extends ShouldBeStored
{
    public function __construct(
        public string $name
    ) {
    }
}

class CartCleared extends ShouldBeStored
{
}
