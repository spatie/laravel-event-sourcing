<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Tests\TestClasses\Events\TestEvent;

class ChildEventHandlingMethodsProjector extends EventHandlingMethodsProjector
{
    public function onTestEvent(TestEvent $event): void
    {
        static::$log[] = 'onTestEvent';
    }
}
