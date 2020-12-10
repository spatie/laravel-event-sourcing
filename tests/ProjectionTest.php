<?php

namespace Spatie\EventSourcing\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\EventSourcing\Projections\Exceptions\ReadonlyProjection;
use Spatie\EventSourcing\Tests\TestClasses\Models\ProjectionModel;

class ProjectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('projection_models');

        Schema::create('projection_models', function (Blueprint $table): void {
            (new ProjectionModel())->getBlueprint($table);
        });
    }

    /** @test */
    public function test_create(): void
    {
        $this->assertExceptionThrown(function (): void {
            ProjectionModel::create([
                'field' => 'test',
            ]);
        }, ReadonlyProjection::class);

        $this->assertDatabaseCount((new ProjectionModel())->getTable(), 0);

        $model = ProjectionModel::new()->writeable()->create([
            'field' => 'test',
        ]);

        $this->assertTrue($model->exists);
    }

    /** @test */
    public function test_save(): void
    {
        $projection = $this->createProjection();

        $this->assertExceptionThrown(function () use ($projection): void {
            $projection->field = 'changed';

            $projection->save();
        }, ReadonlyProjection::class);

        $this->assertNothingChanged();

        $projection->field = 'changed';

        $projection->writeable()->save();

        $this->assertEquals('changed', $projection->refresh()->field);
    }

    /** @test */
    public function test_update(): void
    {
        $projection = $this->createProjection();

        $this->assertExceptionThrown(function () use ($projection): void {
            $projection->update([
                'field' => 'changed',
            ]);
        }, ReadonlyProjection::class);

        $this->assertNothingChanged();

        $projection->writeable()->update([
            'field' => 'changed',
        ]);

        $this->assertEquals('changed', $projection->refresh()->field);
    }

    /** @test */
    public function test_delete(): void
    {
        $projection = $this->createProjection();

        $this->assertExceptionThrown(function () use ($projection): void {
            $projection->delete();
        }, ReadonlyProjection::class);

        $this->assertNothingChanged();

        $projection->writeable()->delete();

        $this->assertEquals(0, ProjectionModel::all()->count());
    }

    /** @test */
    public function test_force_delete(): void
    {
        $projection = $this->createProjection();

        $this->assertExceptionThrown(function () use ($projection): void {
            $projection->forceDelete();
        }, ReadonlyProjection::class);

        $this->assertNothingChanged();

        $projection->writeable()->forceDelete();

        $this->assertEquals(0, ProjectionModel::all()->count());
    }

    /** @test */
    public function test_force_fill(): void
    {
        $projection = $this->createProjection();

        $this->assertExceptionThrown(function () use ($projection): void {
            $projection->forceFill([
                'field' => 'changed',
            ])->save();
        }, ReadonlyProjection::class);

        $this->assertNothingChanged();

        $projection->writeable()->forceFill([
            'field' => 'changed',
        ])->save();

        $this->assertEquals('changed', $projection->refresh()->field);
    }

    /** @test */
    public function is_writeable_is_reset_on_refresh(): void
    {
        $projection = $this->createProjection();

        $projection = $projection->writeable();

        $this->assertFalse($projection->refresh()->isWriteable());
    }

    /** @test */
    public function is_writeable_is_reset_on_fresh(): void
    {
        $projection = $this->createProjection();

        $projection = $projection->writeable();

        $this->assertFalse($projection->fresh()->isWriteable());
    }

    /** @test */
    public function test_read(): void
    {
        $this->createProjection();

        $this->assertEquals(1, ProjectionModel::all()->count());
    }

    private function createProjection(): ProjectionModel
    {
        ProjectionModel::new()->writeable()->create([
            'id' => 1,
            'field' => 'original',
        ]);

        return ProjectionModel::find(1);
    }

    private function assertNothingChanged(): void
    {
        $this->assertDatabaseHas((new ProjectionModel())->getTable(), [
            'id' => 1,
            'field' => 'original',
        ]);
    }
}
