<?php

namespace Spatie\EventProjector\Console;

use Mockery;
use Illuminate\Support\Facades\Artisan;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Facades\EventProjector;
use Spatie\EventProjector\Tests\TestClasses\Mutators\BalanceMutator;

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

        EventProjector::addMutator(get_class($mutator));
        */
    }

    /** @test */
    public function it_will_not_replay_any_events_if_there_are_no_mutators_given()
    {
        $this->artisan('event-projector:replay-events');

        $this->assertSee('No mutators found');
    }

    protected function assertSee(string $text)
    {
        $this->assertContains($text, Artisan::output());
    }
}
