<?php

namespace Spatie\EventProjector\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Model;

final class Account extends Model
{
    public $guarded = [];

    public function addMoney(int $amount): self
    {
        $this->amount += $amount;

        $this->save();

        return $this;
    }

    public function subtractMoney(int $amount): self
    {
        $this->amount -= $amount;

        $this->save();

        return $this;
    }

    public function isBroke(): bool
    {
        return $this->amount < 0;
    }
}
