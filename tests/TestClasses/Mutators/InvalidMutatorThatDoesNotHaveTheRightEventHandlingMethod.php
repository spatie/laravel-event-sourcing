<?php

namespace Spatie\EventSorcerer\Tests\TestClasses\Mutators;

use Spatie\EventSorcerer\Tests\TestClasses\Events\MoneyAdded;

class InvalidMutatorThatDoesNotHaveTheRightEventHandlingMethod
{
    public $handlesEvents = [
        MoneyAdded::class => 'hahaThisMethodDoesNotExist',
    ];
}
