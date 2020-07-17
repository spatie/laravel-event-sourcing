---
title: Making sure events get handled synchronously
weight: 4
---

By default, reactors will handle events asynchronously: when you fire off an event in a request, all reactors will get called via a dispatched job.

## Handling events synchronously

In some situations, you may want to handle the events synchronously by reactors. For example, in a cross domain context: you have an account domain and an e-wallet domain. When balance is deducted in account domain, you want the e-wallet domain to reacts immediately. In this case, you can create a `SyncReactor`.


```php

namespace App\Reactors;

use App\Account;
use App\Wallet;
use App\Events\MoneyAdded;
use Spatie\EventSourcing\Reactors\SyncReactor;
use Spatie\EventSourcing\EventHandlers\HandlesEvents;

final class CreditWalletReactor implements SyncReactor
{
    use HandlesEvents;

    public function onMoneyAdded(MoneyAdded $event)
    {
        $ewallet = Wallet::uuid($event->walletUuid);

        $ewallet->add($event->amount);
    }
}
``` 

 
