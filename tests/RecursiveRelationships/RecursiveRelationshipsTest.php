<?php

namespace SolutionForest\InspireCms\Support\Tests\RecursiveRelationships;

use SolutionForest\InspireCms\Support\Tests\TestCase;
use SolutionForest\InspireCms\Support\Tests\TestModels\HasRecursiveRelationships\TestHasSoftDelete;
use SolutionForest\InspireCms\Support\Tests\TestModels\HasRecursiveRelationships\TestNormal;

class RecursiveRelationshipsTest extends TestCase
{
    public function test_creating_sets_parent_id_if_blank()
    {
        $model = TestNormal::create([
            'name' => 'Parent is null',
        ]);

        $this->assertNull($model->parent_id);
    }

    public function test_deleting_deletes_children()
    {
        $parent = TestNormal::create([
            'name' => 'Parent',
        ]);

        $child = TestNormal::create([
            'name' => 'Child',
            'parent_id' => $parent->id,
        ]);

        $tableName = $parent->getTable();

        $parent->delete();

        $this->assertDatabaseEmpty($tableName);
    }

    public function test_force_deleting_force_deletes_children()
    {
        $parent = TestHasSoftDelete::create([
            'name' => 'Parent',
        ]);

        $child = TestHasSoftDelete::create([
            'name' => 'Child',
            'parent_id' => $parent->id,
        ]);

        $tableName = $parent->getTable();

        $parent->forceDelete();

        $this->assertDatabaseEmpty($tableName);
    }

    public function test_restoring_restores_parent()
    {
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

        $this->assertSoftDeleted($tableName, ['id' => $parentId]);
        $this->assertSoftDeleted($tableName, ['id' => $childId]);

        $child->restore();

        $this->assertNotSoftDeleted($tableName, ['id' => $parentId]);
        $this->assertNotSoftDeleted($tableName, ['id' => $childId]);
    }
}
