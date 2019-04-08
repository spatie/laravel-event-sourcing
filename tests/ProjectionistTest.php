<?php

namespace Spatie\EventProjector\Tests;

use Mockery;
use Exception;
use ReflectionException;
use Illuminate\Support\Facades\Queue;
use Spatie\EventProjector\HandleStoredEventJob;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\MoneyAddedCountProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ProjectorThatThrowsAnException;
use Spatie\EventProjector\Tests\TestClasses\Projectors\InvalidProjectorThatDoesNotHaveTheRightEventHandlingMethod;

final class ProjectionistTest extends TestCase
{
    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

    public function setUp(): void
    {
        parent::setUp();

        $this->account = Account::create();
    }

    /** @test */
    public function it_will_throw_an_exception_when_trying_to_add_a_non_existing_projector()
    {
        $this->expectException(ReflectionException::class);

        Projectionist::addProjector('non-exising-class-name');
    }

    /** @test */
    public function it_will_thrown_an_exception_when_trying_to_add_a_non_existing_reactor()
    {
        $this->expectException(ReflectionException::class);

        Projectionist::addReactor('non-exising-class-name');
    }

    /** @test */
    public function it_will_thrown_an_exception_when_an_event_handler_does_not_have_the_expected_event_handling_method()
    {
        $this->expectException(ReflectionException::class);

        Projectionist::addProjector(InvalidProjectorThatDoesNotHaveTheRightEventHandlingMethod::class);

        event(new MoneyAddedEvent($this->account, 1234));
    }

    /** @test */
    public function it_will_not_register_the_same_projector_twice()
    {
        Projectionist::addProjector(BalanceProjector::class);
        Projectionist::addProjector(BalanceProjector::class);

        $this->assertCount(1, Projectionist::getProjectors());
    }

    /** @test */
    public function it_will_not_register_the_same_reactor_twice()
    {
        Projectionist::addReactor(BrokeReactor::class);
        Projectionist::addReactor(BrokeReactor::class);

        $this->assertCount(1, Projectionist::getReactors());
    }

    /** @test */
    public function it_will_call_the_method_on_the_projector_when_the_projector_throws_an_exception()
    {
        $this->setConfig('event-projector.catch_exceptions', true);

        $projector = Mockery::mock(ProjectorThatThrowsAnException::class.'[handleException]');

        $projector->shouldReceive('handleException')->once();

        Projectionist::addProjector($projector);

        event(new MoneyAddedEvent($this->account, 1000));
    }

    /** @test */
    public function it_can_catch_exceptions_and_still_continue_calling_other_projectors()
    {
        $this->setConfig('event-projector.catch_exceptions', true);

        $failingProjector = new ProjectorThatThrowsAnException();
        Projectionist::addProjector($failingProjector);

        $workingProjector = new BalanceProjector();
        Projectionist::addProjector($workingProjector);

        event(new MoneyAddedEvent($this->account, 1000));

        $this->assertEquals(1000, $this->account->refresh()->amount);
    }

    /** @test */
    public function it_can_not_catch_exceptions_and_not_continue()
    {
        $failingProjector = new ProjectorThatThrowsAnException();
        Projectionist::addProjector($failingProjector);

        $this->expectException(Exception::class);

        event(new MoneyAddedEvent($this->account, 1000));
    }

    /** @test */
    public function projectors_that_dont_handle_fired_events_are_handled_correctly()
    {
        Projectionist::addProjector(MoneyAddedCountProjector::class);

        event(new MoneySubtractedEvent($this->account, 500));

        $this->assertEquals(0, $this->account->fresh()->addition_count);
    }

    /** @test */
    public function it_propagates_custom_event_tags_to_event_job()
    {
        Queue::fake();

        Projectionist::addProjector(MoneyAddedCountProjector::class);

        event(new MoneyAddedEvent($this->account, 500));

        Queue::assertPushed(HandleStoredEventJob::class, function (HandleStoredEventJob $job) {
            $expected = [
                'Account:'.$this->account->id,
                MoneyAddedEvent::class,
            ];

            return $expected === $job->tags();
        });
    }

    /** @test */
    public function it_can_remove_all_event_handlers()
    {
        Projectionist::addProjector(MoneyAddedCountProjector::class);
        Projectionist::addProjector(BalanceProjector::class);
        Projectionist::addReactor(BrokeReactor::class);

        $this->assertCount(2, Projectionist::getProjectors());
        $this->assertCount(1, Projectionist::getReactors());

        Projectionist::withoutEventHandlers();

        $this->assertCount(0, Projectionist::getProjectors());
        $this->assertCount(0, Projectionist::getReactors());
    }

    /** @test */
    public function it_can_remove_certain_event_handlers()
    {
        Projectionist::addProjector(MoneyAddedCountProjector::class);
        Projectionist::addProjector(BalanceProjector::class);
        Projectionist::addReactor(BrokeReactor::class);

        $this->assertCount(2, Projectionist::getProjectors());
        $this->assertCount(1, Projectionist::getReactors());

        Projectionist::withoutEventHandlers([MoneyAddedCountProjector::class, BrokeReactor::class]);

        $this->assertCount(1, Projectionist::getProjectors());
        $this->assertEquals(BalanceProjector::class, get_class(Projectionist::getProjectors()->first()));
        $this->assertCount(0, Projectionist::getReactors());

        Projectionist::withoutEventHandler(BalanceProjector::class);
        $this->assertCount(0, Projectionist::getProjectors());
    }
}
