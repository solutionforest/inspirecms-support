<?php

namespace SolutionForest\InspireCms\Support\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\Models\MediaAsset;

class MediaAssetFactory extends Factory
{
    protected $model = MediaAsset::class;

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
