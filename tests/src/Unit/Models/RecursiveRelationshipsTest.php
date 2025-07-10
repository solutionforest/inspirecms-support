<?php

use Illuminate\Support\Facades\DB;
use SolutionForest\InspireCms\Support\Tests\Models\HasRecursiveRelationships\TestHasSoftDelete;
use SolutionForest\InspireCms\Support\Tests\Models\HasRecursiveRelationships\TestNormal;
use SolutionForest\InspireCms\Support\Tests\TestCase;

uses(TestCase::class);
pest()->group('unit');

it('creates and sets parent_id to null if blank', function () {
    $model = TestNormal::create([
        'name' => 'Parent is null',
    ]);

    expect($model->parent_id)->toBeNull();
});

it('deletes parent and children', function () {
    $parent = TestNormal::create([
        'name' => 'Parent',
    ]);

    $child = TestNormal::create([
        'name' => 'Child',
        'parent_id' => $parent->id,
    ]);

    $tableName = $parent->getTable();

    $parent->delete();

    expect(DB::table($tableName)->count())->toBe(0);
});

it('force deletes parent and children', function () {
    $parent = TestHasSoftDelete::create([
        'name' => 'Parent',
    ]);

    $child = TestHasSoftDelete::create([
        'name' => 'Child',
        'parent_id' => $parent->id,
    ]);

    $tableName = $parent->getTable();

    $parent->forceDelete();

    expect(DB::table($tableName)->count())->toBe(0);
});

it('restores parent and children', function () {
    $parent = TestHasSoftDelete::create([
        'name' => 'Parent',
    ]);

    $child = TestHasSoftDelete::create([
        'name' => 'Child',
        'parent_id' => $parent->id,
    ]);

    $tableName = $parent->getTable();
    $parentId = $parent->id;
    $childId = $child->id;

    $parent->delete();

    expect(DB::table($tableName)->where('id', $parentId)->first()->deleted_at)->not->toBeNull();
    expect(DB::table($tableName)->where('id', $childId)->first()->deleted_at)->not->toBeNull();

    $child->restore();

    expect(DB::table($tableName)->where('id', $parentId)->first()->deleted_at)->toBeNull();
    expect(DB::table($tableName)->where('id', $childId)->first()->deleted_at)->toBeNull();
});
