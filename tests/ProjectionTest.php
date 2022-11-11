<?php

namespace Spatie\EventSourcing\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

use Spatie\EventSourcing\Projections\Exceptions\ReadonlyProjection;
use Spatie\EventSourcing\Tests\TestClasses\Models\ProjectionModel;

function createProjection(): ProjectionModel
{
    ProjectionModel::new()->writeable()->create([
        'uuid' => 'test-uuid',
        'field' => 'original',
    ]);

    return ProjectionModel::find('test-uuid');
}

function assertNothingChanged(): void
{
    test()->assertDatabaseHas((new ProjectionModel())->getTable(), [
        'uuid' => 'test-uuid',
        'field' => 'original',
    ]);
}

beforeEach(function () {
    Schema::dropIfExists('projection_models');

    Schema::create('projection_models', function (Blueprint $table): void {
        (new ProjectionModel())->getBlueprint($table);
    });
});

it('can create', function () {
    expect(function () {
        ProjectionModel::create([
            'uuid' => 'test-uuid',
            'field' => 'test',
        ]);
    })->toThrow(ReadonlyProjection::class);

    $this->assertDatabaseCount((new ProjectionModel())->getTable(), 0);

    $model = ProjectionModel::new()->writeable()->create([
        'uuid' => 'test-uuid-2',
        'field' => 'test',
    ]);

    assertTrue($model->exists);
});

it('can save', function () {
    $projection = createProjection();

    expect(function () use ($projection) {
        $projection->field = 'changed';

        $projection->save();
    })->toThrow(ReadonlyProjection::class);

    assertNothingChanged();

    $projection->field = 'changed';

    $projection->writeable()->save();

    assertEquals('changed', $projection->refresh()->field);
});

it('can update', function () {
    $projection = createProjection();

    expect(function () use ($projection) {
        $projection->update([
            'field' => 'changed',
        ]);
    })->toThrow(ReadonlyProjection::class);

    assertNothingChanged();

    $projection->writeable()->update([
        'field' => 'changed',
    ]);

    assertEquals('changed', $projection->refresh()->field);
});

it('can delete', function () {
    $projection = createProjection();

    expect(fn () => $projection->delete())->toThrow(ReadonlyProjection::class);

    assertNothingChanged();

    $projection->writeable()->delete();

    assertEquals(0, ProjectionModel::all()->count());
});

it('can force delete', function () {
    $projection = createProjection();

    expect(fn () => $projection->forceDelete())->toThrow(ReadonlyProjection::class);

    assertNothingChanged();

    $projection->writeable()->forceDelete();

    assertEquals(0, ProjectionModel::all()->count());
});

it('can force fill', function () {
    $projection = createProjection();

    expect(function () use ($projection) {
        $projection->forceFill([
            'field' => 'changed',
        ])->save();
    })->toThrow(ReadonlyProjection::class);

    assertNothingChanged();

    $projection->writeable()->forceFill([
        'field' => 'changed',
    ])->save();

    assertEquals('changed', $projection->refresh()->field);
});

it('should reset is writeable on refresh', function () {
    $projection = createProjection();

    $projection = $projection->writeable();

    assertFalse($projection->refresh()->isWriteable());
});

it('should reset is writeable on fresh', function () {
    $projection = createProjection();

    $projection = $projection->writeable();

    assertFalse($projection->fresh()->isWriteable());
});

it('can read', function () {
    createProjection();

    assertEquals(1, ProjectionModel::all()->count());
});
