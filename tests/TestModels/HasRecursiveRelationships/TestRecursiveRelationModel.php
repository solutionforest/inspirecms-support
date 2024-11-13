<?php

namespace SolutionForest\InspireCms\Support\Tests\TestModels\HasRecursiveRelationships;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface;
use SolutionForest\InspireCms\Support\Models\Concerns\HasRecursiveRelationships;

class TestRecursiveRelationModel extends Model implements HasRecursiveRelationshipsInterface
{
    use HasRecursiveRelationships;

    protected $guarded = [];

    protected $table = 'test_recursive_relation_models';

    public function getRootLevelParentId()
    {
        return null;
    }
}
