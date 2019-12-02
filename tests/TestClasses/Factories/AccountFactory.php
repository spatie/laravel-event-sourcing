<?php

namespace Spatie\EventSourcing\Tests\Factories;

use Illuminate\Support\Collection;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;

final class AccountFactory
{
    public static function create(int $amount = 1): Collection
    {
        return collect(range(1, $amount))->map(fn() => new Account());
    }
}
