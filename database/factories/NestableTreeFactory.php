<?php

namespace SolutionForest\InspireCms\Support\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NestableTreeFactory extends Factory
{

    public function definition()
    {
        return [
            'order' => 1,
            'parent_id' => 0,
        ];
    }
}
