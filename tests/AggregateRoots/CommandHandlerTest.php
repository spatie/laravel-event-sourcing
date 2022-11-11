<?php

namespace Spatie\EventSourcing\Tests\AggregateRoots;

use Spatie\EventSourcing\AggregateRoots\AggregatePartial;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\Commands\AggregateUuid;
use Spatie\EventSourcing\Commands\CommandBus;
use Spatie\EventSourcing\Commands\HandledBy;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use function PHPUnit\Framework\assertCount;

beforeAll(function () {
    #[HandledBy(CartForCommand::class)]
    class AddItem
    {
        public function __construct(
            #[AggregateUuid] public string $cartUuid,
            public string $name
        ) {
        }
    }

    #[HandledBy(CartForCommand::class)]
    class ClearCart
    {
        public function __construct(
            #[AggregateUuid] public string $cartUuid
        ) {
        }
    }

    class CartForCommand extends AggregateRoot
    {
        // Public for testing
        public CartItemsForCommand $cartItems;

        public function __construct()
        {
            $this->cartItems = new CartItemsForCommand($this);
        }

        public function clear(ClearCart $clearCart): self
        {
            $this->recordThat(new CartClearedForCommand());

            return $this;
        }

        public function applyClear(CartClearedForCommand $event): void
        {
            $this->cartItems = new CartItemsForCommand($this);
        }
    }

    class CartItemsForCommand extends AggregatePartial
    {
        // Public for testing
        public array $items = [];

        public function addItem(AddItem $addItem)
        {
            $this->recordThat(new ItemAddedForCommand($addItem->name));
        }

        public function applyAddItem(ItemAddedForCommand $itemAdded)
        {
            $this->items[] = $itemAdded->name;
        }
    }

    class ItemAddedForCommand extends ShouldBeStored
    {
        public function __construct(
            public string $name
        ) {
        }
    }

    class CartClearedForCommand extends ShouldBeStored
    {
    }
});

beforeEach(function () {
    $this->UUID = 'cart-uuid';
});

it('should dispatch command to aggregate', function () {
    $bus = new CommandBus();

    CartForCommand::retrieve($this->UUID)->persist();

    // Assert that commands are dispatched to entities
    $bus->dispatch(new AddItem(
        $this->UUID,
        'name'
    ));

    assertCount(1, CartForCommand::retrieve($this->UUID)->cartItems->items);

    // Assert that commands are dispatched to the AR iteself
    $bus->dispatch(new ClearCart(
        $this->UUID,
    ));

    assertCount(0, CartForCommand::retrieve($this->UUID)->cartItems->items);
});
