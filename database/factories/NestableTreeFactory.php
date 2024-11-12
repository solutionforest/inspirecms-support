<?php

namespace SolutionForest\InspireCms\Support\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SolutionForest\InspireCms\Support\Models\Polymorphic\NestableTree;

class NestableTreeFactory extends Factory
{
    protected $model = NestableTree::class;

    public function definition()
    {
        return [
            'order' => 1,
            'parent_id' => 0,
        ];
    }
}
