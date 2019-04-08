<?php

namespace Spatie\EventProjector\Tests\Factories;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;

final class AccountFactory
{
    public static function create(int $amount = 1): Collection
    {
        return collect(range(1, $amount))->map(function () {
            return new Account();
        });
    }
}
