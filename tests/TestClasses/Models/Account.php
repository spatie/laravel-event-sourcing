<?php

namespace Spatie\EventSorcerer\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
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
