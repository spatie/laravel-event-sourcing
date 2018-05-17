<?php

namespace Spatie\EventSorcerer\Tests;

use Spatie\EventSorcerer\Exceptions\InvalidEventHandler;
use Spatie\EventSorcerer\Facades\EventSorcerer;

class EventSorcererTest extends TestCase
{
    /** @test */
    public function it_will_thrown_an_exception_when_trying_to_add_a_non_existing_mutator()
    {
        $this->expectException(InvalidEventHandler::class);

        EventSorcerer::addMutator('non-exising-class-name');
    }

    /** @test */
    public function it_will_thrown_an_exception_when_trying_to_add_a_non_existing_reactor()
    {
        $this->expectException(InvalidEventHandler::class);

        EventSorcerer::addReactor('non-exising-class-name');
    }
}