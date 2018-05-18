<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;

class InvalidProjectorThatDoesNotHaveTheRightEventHandlingMethod
{
    public $handlesEvents = [
        MoneyAdded::class => 'hahaThisMethodDoesNotExist',
    ];
}
