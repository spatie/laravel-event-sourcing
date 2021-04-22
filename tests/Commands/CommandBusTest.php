<?php

namespace Spatie\EventSourcing\Tests\Commands;

use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Support\Facades\DB;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\Commands\AggregateUuid;
use Spatie\EventSourcing\Commands\CommandBus;
use Spatie\EventSourcing\Commands\HandledBy;
use Spatie\EventSourcing\Commands\Middleware\RetryMiddleware;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\Tests\Support\Async\Fork;
use Spatie\EventSourcing\Tests\TestCase;

class CommandBusTest extends TestCase
{
    use InteractsWithExceptionHandling;

    public const UUID = 'cart-uuid';

    /** @test */
    public function it()
    {
        $bus = app(CommandBus::class);

        $bus->dispatch(new AddItem('cart-uuid', 'item-1'));
    }

    /** @test */
    public function test_retry()
    {
        $bus = app(CommandBus::class)->middleware(new RetryMiddleware());

        Fork::new()
            ->before(fn () => DB::connection('mysql')->reconnect())
            ->run(
                fn () => $bus->dispatch(new AddItem('cart-uuid', 'item-1')),
                fn () => $bus->dispatch(new AddItem('cart-uuid', 'item-2')),
            );

        $cart = Cart::retrieve(self::UUID);

        $this->assertCount(2, $cart->items);
    }
}

#[HandledBy(Cart::class)]
class AddItem
{
    public function __construct(
        #[AggregateUuid] public string $cartUuid,
        public string $name
    ) {
    }
}

class Cart extends AggregateRoot
{
    public array $items;

    public function add(AddItem $addItem): self
    {
        $this->recordThat(new ItemAdded($addItem->name));

        return $this;
    }

    protected function applyItemAdded(ItemAdded $itemAdded): void
    {
        $this->items[] = $itemAdded->name;
    }
}

class ItemAdded extends ShouldBeStored
{
    public function __construct(
        public string $name
    ) {
    }
}
