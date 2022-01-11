<?php

namespace Spatie\EventSourcing\Tests\AggregateRoots;

use Exception;
use Spatie\EventSourcing\AggregateRoots\AggregatePartial;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\Attributes\IncludeInSnapshot;
use Spatie\EventSourcing\Snapshots\EloquentSnapshot;
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

        $this->assertDatabaseCount((new EloquentStoredEvent())->getTable(), 2);

        $cart::retrieve(self::CART_UUID);

        $cartItems = $cart->getCartItems()->getItems();
        $this->assertCount(2, $cartItems);
        $this->assertEquals('test', $cartItems[0]);
        $this->assertEquals('test 2', $cartItems[1]);

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

    /** @test */
    public function snapshotting_aggregate_partial_and_version_number()
    {
        $cart = Cart::retrieve(self::CART_UUID);

        $cart
            ->addItem('test')
            ->addItem('test 2')
            ->addItem('test 3');

        $this->assertEquals(0, EloquentSnapshot::count());

        $cart->snapshot();

        $this->assertEquals(1, EloquentSnapshot::count());
        tap(EloquentSnapshot::first(), function (EloquentSnapshot $snapshot) {
            $this->assertIsArray($snapshot->state['cartItems']);
            $this->assertIsArray($snapshot->state['cartItems']['items']);
            $this->assertCount(3, $snapshot->state['cartItems']['items']);
            $this->assertEquals('test', $snapshot->state['cartItems']['items'][0]);
            $this->assertEquals('test 2', $snapshot->state['cartItems']['items'][1]);
            $this->assertEquals('test 3', $snapshot->state['cartItems']['items'][2]);

            $this->assertIsArray($snapshot->state['cartItems']);
            $this->assertIsArray($snapshot->state['cartItems']['internalItems']);
            $this->assertCount(3, $snapshot->state['cartItems']['internalItems']);
            $this->assertEquals('test', $snapshot->state['cartItems']['internalItems'][0]);
            $this->assertEquals('test 2', $snapshot->state['cartItems']['internalItems'][1]);
            $this->assertEquals('test 3', $snapshot->state['cartItems']['internalItems'][2]);

            $this->assertEquals(3, $snapshot->aggregate_version);
        });

    }

    /** @test */
    public function restoring_an_aggregate_root_with_a_snapshot_restores_aggregate_partial()
    {
        $cart = Cart::retrieve(self::CART_UUID);

        $cart
            ->addItem('test')
            ->addItem('test 2')
            ->addItem('test 3');

        $this->assertEquals(0, EloquentSnapshot::count());

        $cart->snapshot();

        $cartRetrieved = Cart::retrieve(self::CART_UUID);

        $cartItems = $cartRetrieved->getCartItems()->getItems();
        $this->assertCount(3, $cartItems);
        $this->assertEquals('test', $cartItems[0]);
        $this->assertEquals('test 2', $cartItems[1]);
        $this->assertEquals('test 3', $cartItems[2]);

        $internalItems = $cartRetrieved->getCartItems()->getInternalItems();
        $this->assertCount(3, $internalItems);
        $this->assertEquals('test', $internalItems[0]);
        $this->assertEquals('test 2', $internalItems[1]);
        $this->assertEquals('test 3', $internalItems[2]);
    }
}

class Cart extends AggregateRoot
{
    protected CartItems $cartItems;

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
        $this->recordThat(new CartCleared());

        return $this;
    }

    public function applyClear(CartCleared $event): void
    {
        $this->cartItems = new CartItems($this);
    }

    // Public getter for testing only
    public function getCartItems(): CartItems
    {
        return $this->cartItems;
    }
}

class CartItems extends AggregatePartial
{

    public array $items = [];
    #[IncludeInSnapshot]
    protected array $internalItems = [];

    public function addItem(string $name)
    {
        $this->recordThat(new ItemAdded($name));
    }

    public function applyAddItem(ItemAdded $itemAdded)
    {
        $this->items[] = $itemAdded->name;
        $this->internalItems[] = $itemAdded->name;
    }

    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }

    // Public getter for testing only
    public function getItems(): array
    {
        return $this->items;
    }

    // Public getter for testing only
    public function getInternalItems(): array
    {
        return $this->internalItems;
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
