<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ResettableProjector;

class RebuildCommandTest extends TestCase
{
    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = Account::create();
    }

    /** @test */
    public function it_can_rebuild_a_projector()
    {
        Projectionist::addProjector(ResettableProjector::class);

        event(new MoneyAdded($this->account, 1000));

        $this->artisan('event-projector:rebuild', [
            'projector' => [ResettableProjector::class],
        ]);

        $this->assertSeeInConsoleOutput('Projector(s) rebuild!');

        $this->assertCount(1, ProjectorStatus::get());
    }

    /** @test */
    public function it_allows_leading_slashes()
    {
        Projectionist::addProjector(ResettableProjector::class);

        $this->artisan('event-projector:rebuild', [
            'projector' => ['\\'.ResettableProjector::class],
        ]);

        $this->assertSeeInConsoleOutput('Projector(s) rebuild!');
    }

    /** @test */
    public function a_projector_status_will_not_be_created_after_a_projector_is_rebuild_without_any_events()
    {
        Projectionist::addProjector(ResettableProjector::class);

        ProjectorStatus::getForProjector(new ResettableProjector());

        $this->assertCount(1, ProjectorStatus::get());

        $this->artisan('event-projector:rebuild', [
            'projector' => [ResettableProjector::class],
        ]);

        $this->assertSeeInConsoleOutput('Projector(s) rebuild!');

        $this->assertCount(0, ProjectorStatus::get());
    }
}
