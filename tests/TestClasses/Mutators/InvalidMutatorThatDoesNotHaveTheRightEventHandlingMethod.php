<?php

namespace Spatie\EventProjector\Tests\TestClasses\Mutators;

use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;

class InvalidMutatorThatDoesNotHaveTheRightEventHandlingMethod
{
    public $handlesEvents = [
        MoneyAdded::class => 'hahaThisMethodDoesNotExist',
    ];
}
