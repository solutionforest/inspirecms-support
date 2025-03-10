<?php

namespace SolutionForest\InspireCms\Support\Tests\Models\HasRecursiveRelationships;

use Illuminate\Database\Eloquent\SoftDeletes;

class TestHasSoftDelete extends TestRecursiveRelationModel
{
    use SoftDeletes;
}
