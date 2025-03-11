---
title: Writing your first projector
weight: 1
---

This section is a perfect entry point to get yourself acquainted with projectors. Most examples in these docs are also available in the Laravel app you'll find in [this repo on GitHub](https://github.com/spatie/larabank-projectors). Clone that repo to toy around with the package.

A projector is a class that gets triggered when new events come in. It typically writes data (to the database or to a file on disk). We call that written data a projection.

Projections are based on the event stream, and we're not allowed to make changes to them from outside that stream. To prevent us from forgetting this rule, the package introduces a new class called `Projection`.
This class extends Eloquent's `Model`, and includes functionality that prevents you saving a projection that is not based on the event stream. Saving without calling `writeable()` will throw an exception.

Imagine you are a bank with customers that have accounts. All these accounts have a balance. When money gets added or subtracted we could modify the balance. If we do that however, we would never know why the balance got to that number. If we were to store all the transactions as events we could calculate the balance.

## Creating a model

Here's a small migration to create a table that stores accounts. Using a `uuid` is not strictly required, but it will make your life much easier when using this package. In all examples we'll assume that you'll use them.

```php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->string('name');
            $table->integer('balance')->default(0);
            $table->timestamps();
        });
    }
}
```

The `Account` model itself could look like this:

```php
namespace App;

use App\Events\AccountCreated;
use App\Events\AccountDeleted;
use App\Events\MoneyAdded;
use App\Events\MoneySubtracted;
use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\Projections\Projection;

class Account extends Projection
{
    protected $guarded = [];

    public static function createWithAttributes(array $attributes): Account
    {
        /*
         * Let's generate a uuid.
         */
        $attributes['uuid'] = (string) Uuid::uuid4();

        /*
         * The account will be created inside this event using the generated uuid.
         */
        event(new AccountCreated($attributes));

        /*
         * The uuid will be used the retrieve the created account.
         */
        return static::uuid($attributes['uuid']);
    }

    public function addMoney(int $amount)
    {
        event(new MoneyAdded($this->uuid, $amount));
    }

    public function subtractMoney(int $amount)
    {
        event(new MoneySubtracted($this->uuid, $amount));
    }

    public function remove()
    {
        event(new AccountDeleted($this->uuid));
    }

    /*
     * A helper method to quickly retrieve an account by uuid.
     */
    public static function uuid(string $uuid): ?Account
    {
        return static::where('uuid', $uuid)->first();
    }
}
```

## Defining events

Instead of creating, updating and deleting accounts, we're simply firing off events. All these events should extend `\Spatie\EventSourcing\StoredEvents\ShouldBeStored`. This abstract class signifies to our package that the event should be stored.

Let's take a look at all events used in the `Account` model.

```php
namespace App\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class AccountCreated extends ShouldBeStored
{
    /** @var array */
    public $accountAttributes;

    public function __construct(array $accountAttributes)
    {
        $this->accountAttributes = $accountAttributes;
    }
}
```

```php
namespace App\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class MoneyAdded extends ShouldBeStored
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

```php
namespace App\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class MoneySubtracted extends ShouldBeStored
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

```php
namespace App\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class AccountDeleted extends ShouldBeStored
{
    /** @var string */
    public $accountUuid;

    public function __construct(string $accountUuid)
    {
        $this->accountUuid = $accountUuid;
    }
}
```

## Creating your first projector

A projector is a class that listens for events that were stored. When it hears an event that it is interested in, it can perform some work.

Let's create your first projector. You can perform `php artisan make:projector AccountBalanceProjector` to create a projector in `app\Projectors`.

Here's an example projector that handles all the events mentioned above:

```php
namespace App\Projectors;

use App\Account;
use App\Events\AccountCreated;
use App\Events\AccountDeleted;
use App\Events\MoneyAdded;
use App\Events\MoneySubtracted;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class AccountBalanceProjector extends Projector
{
    public function onAccountCreated(AccountCreated $event)
    {
        (new Account($event->accountAttributes))->writeable()->save();
    }

    public function onMoneyAdded(MoneyAdded $event)
    {
        $account = Account::uuid($event->accountUuid);

        $account->balance += $event->amount;

        $account->writeable()->save();
    }

    public function onMoneySubtracted(MoneySubtracted $event)
    {
        $account = Account::uuid($event->accountUuid);

        $account->balance -= $event->amount;

        $account->writeable()->save();
    }

    public function onAccountDeleted(AccountDeleted $event)
    {
        Account::uuid($event->accountUuid)->writeable()->delete();
    }
}
```

Just by type hinting an event in a method will make the package call that method when the event occurs. As in the example above, make sure the method parameter name is `$event`. By default the package will automatically discover and register projectors.

## Let's fire off some events

With all this out of the way we can fire off some events.

Let's try adding an account with:

```php
Account::createWithAttributes(['name' => 'Luke']);
Account::createWithAttributes(['name' => 'Leia']);
```

And let's make some transactions on that account:

```php
$account = Account::where(['name' => 'Luke'])->first();
$anotherAccount = Account::where(['name' => 'Leia'])->first();

$account->addMoney(1000);
$anotherAccount->addMoney(500);
$account->subtractMoney(50);
```

If you take a look at the contents of the `accounts` table you should see some accounts together with their calculated balance. Sweet! In the `stored_events` table you should see an entry per event that we fired.

## Your second projector

Imagine that, after a while, someone at the bank wants to know which accounts have processed the most transactions. Because we stored all changes to the accounts in the events table we can easily get that info by creating another projector.

We are going to create another projector that stores the transactions count per account in a model. Bear in mind that you can easily use any other storage mechanism instead of a model. The projector doesn't care what you use.

Here's the migration and the model class that the projector is going to use:

```php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsCountTable extends Migration
{
    public function up()
    {
        Schema::create('transactions_count', function (Blueprint $table) {
            $table->increments('id');
            $table->string('account_uuid');
            $table->integer('count')->default(0);
            $table->timestamps();
        });
    }
}
```

If you're following along don't forget to run this new migration.

```php
php artisan migrate
```

```php
namespace App;

use Spatie\EventSourcing\Projections\Projection;

class TransactionCount extends Projection
{
    protected $table = 'transactions_count';
    protected $guarded = [];
}
```

Here's the projector that is going to listen to the `MoneyAdded` and `MoneySubtracted` events. Typehinting `MoneyAdded` and `MoneySubtracted`  will make our package call `onMoneyAdded` and `onMoneySubtracted` when these events occur.


```php
namespace App\Projectors;

use App\Events\MoneyAdded;
use App\Events\MoneySubtracted;
use App\TransactionCount;
use Spatie\EventSourcing\Models\StoredEvent;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class TransactionCountProjector extends Projector
{
    public function onMoneyAdded(MoneyAdded $event)
    {
        $transactionCounter = TransactionCount::firstOrCreate(['account_uuid' => $event->accountUuid]);

        $transactionCounter->count += 1;

        $transactionCounter->writeable()->save();
    }

    public function onMoneySubtracted(MoneySubtracted $event)
    {
        $transactionCounter = TransactionCount::firstOrCreate(['account_uuid' => $event->accountUuid]);

        $transactionCounter->count += 1;

        $transactionCounter->writeable()->save();
    }
}
```

Let's not forget to register this projector:

```php
// in a service provider of your own
Projectionist::addProjector(TransactionCountProjector::class);
```

If you've followed along, you've already created some accounts and some events. To feed those past events to the projector we can simply perform this artisan command:

```php
php artisan event-sourcing:replay App\\Projectors\\TransactionCountProjector
```

This command will take all events stored in the `stored_events` table and pass them to `TransactionCountProjector`. After the command completes you should see the transaction counts in the `transactions_count` table.

## Welcoming new events

Now that both of your projections have handled all events, try firing off some new events.

```
Account::createWithAttributes(['name' => 'Yoda']);
```

And let's add some transactions to that account:

```php
$yetAnotherAccount = Account::where(['name' => 'Yoda'])->first();

$yetAnotherAccount->addMoney(1000);
$yetAnotherAccount->subtractMoney(50);
```

You'll notice that both projectors are immediately handling these new events. The balance of the `Account` model is up to date and the data in the `transactions_count` table gets updated.

## Benefits of projectors and projections

The cool thing about projectors is that you can write them after events have happened. Imagine that someone at the bank wants to have a report of the average balance of each account. You would be able to write a new projector, replay all events, and have that data.

Projections are very fast to query. Imagine that our application has processed millions of events. If you want to create a screen where you display the accounts with the most transactions you can easily query the `transactions_count` table. This way you don't need to fire off some expensive query. The projector will keep the projections (the `transactions_count` table) up to date.

## Using Factories in Tests

In the example above the `Account` model contains the necessary logic to create an `Account`, this pattern may require you to revise how you create test data using model factories. One possible solution is illustrated below.

```php
public function test_can_have_many_accounts()
{
    Account::factory()->times(5)->make()->each(function($account) {
        Account::createWithAttributes($account->toArray());
    });

    $this->assertCount(5, auth()->user()->accounts);
    $this->assertInstanceOf(Account::class, auth()->user()->accounts()->first());
}
```

Another approach is to create a trait for your projection factories and write your tests like your would in a regular CRUD app:
```php
// Trait file:
trait SupportsProjections
{
    public function newModel(array $attributes = [])
    {
        return Factory::newModel([
            'uuid' => fake()->uuid(),
            ...$attributes,
        ])->writeable();
    }
}

// Factory file:
class AccountFactory extends Factory
{
    use SupportsProjections;

    public function definition(): array
    {
        return [...];
    }
}

// Test file:
public function test_can_have_many_accounts()
{
    Account::factory()->times(5)->create();
}
```

## Want to know more?

We discuss projections and complex patterns such as CQRS in depth in our [Event Sourcing in Laravel](https://event-sourcing-laravel.com/) course. In practice, you want to check out these chapters:

- Chapter 05: Storing and Projecting Events 
- Chapter 06: [Projectors in Depth](https://event-sourcing-laravel.com/projectors-in-depth)
- Chapter 14: CQRS

