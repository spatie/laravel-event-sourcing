<?php

namespace Spatie\EventProjector\Console;

use Mockery;
use Illuminate\Support\Facades\Artisan;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

class ReplayEventsCommandTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $account = Account::create();

        foreach(range(1,10) as $i) {
            event(new MoneyAdded($account, 1234));
        }
    }

    /** @test */
    public function it_will_replay_events_to_the_given_projectors()
    {
        $projector = Mockery::mock(BalanceProjector::class);

        $projector->shouldReceive('onMoneyAdded')->andReturnNull()->times(10);

        EventProjectionist::addProjector($projector);

        $this->artisan('event-projector:replay-events', ['--projector' => [get_class($projector)]]);
    }

    /** @test */
    public function it_will_not_replay_any_events_if_there_are_no_projectors_given()
    {
        $this->artisan('event-projector:replay-events');

        $this->assertSee('No projectors found');
    }

    protected function assertSee(string $text)
    {
        $this->assertContains($text, Artisan::output());
    }
}
