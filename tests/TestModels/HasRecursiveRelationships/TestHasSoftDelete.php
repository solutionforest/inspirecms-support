<?php

namespace SolutionForest\InspireCms\Support\Tests\TestModels\HasRecursiveRelationships;

use Illuminate\Database\Eloquent\SoftDeletes;

class TestHasSoftDelete extends TestRecursiveRelationModel
{
    use SoftDeletes;
}
