<?php

namespace SolutionForest\InspireCms\Support\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;

class MediaAssetFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(1),
            'parent_id' => 0,
            'is_folder' => false,
            'author_id' => KeyHelper::generateMinUuid(),
            'author_type' => 'cms_user',
        ];
    }

    public function isFolder(bool $condition = true)
    {
        return $this->state(function (array $attributes) use ($condition) {
            return [
                'is_folder' => $condition,
            ];
        });
    }
}
