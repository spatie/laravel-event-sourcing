<?php

namespace Spatie\EventSorcerer\Console;

use Illuminate\Support\Facades\Artisan;
use Mockery;
use Spatie\EventSorcerer\Facades\EventSorcerer;
use Spatie\EventSorcerer\Tests\TestCase;
use Spatie\EventSorcerer\Tests\TestClasses\Mutators\BalanceMutator;

class ReplayEventsCommandTest extends TestCase
{
    /** @test */
    public function it_will_replay_events_to_the_given_mutators()
    {
        /*
        $mutator = Mockery::mock(BalanceMutator::class)
            ->shouldReceive('onMoneyAdded')
            ->times(1)
            ->getMock();

        EventSorcerer::addMutator(get_class($mutator));
        */
    }

    /** @test */
    public function it_will_not_replay_any_events_if_there_are_no_mutators_given()
    {
        $this->artisan('event-sorcerer:replay-events');

        $this->assertSee('No mutators found');
    }

    protected function assertSee(string $text)
    {
        $this->assertContains($text, Artisan::output());
    }
}