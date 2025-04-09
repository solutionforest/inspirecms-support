<?php

namespace SolutionForest\InspireCms\Support\Dtos;

use SolutionForest\InspireCms\Support\Base\Dtos\BaseModelDto;

/**
 * @extends BaseModelDto<\SolutionForest\InspireCms\Support\Models\MediaAsset,MediaAssetDto>
 */
class MediaAssetDto extends BaseModelDto
{
    /**
     * @var string
     */
    public $uid;

    /**
     * @var ?string
     */
    public $caption;

    /**
     * @var ?string
     */
    public $description;

    /**
     * @var array
     */
    public $meta;

    /**
     * @var array
     */
    public $responsive;

    /**
     * @var string
     */
    public $disk;

    public static function fromModel($model)
    {
        $media = $model->getFirstMedia();

        return parent::fromArray([
            'uid' => $model->getKey(),
            'caption' => $model->caption,
            'description' => $model->description,
            'meta' => $media?->manipulations,
            'responsive' => array_keys($media?->responsive_images ?? []),
            'disk' => $media?->disk,
        ])->setModel($model);
    }

    public function getUrl(string $conversionName = ''): ?string
    {
        try {
            return route('inspirecms.asset', [
                'key' => $this->model?->getKey(),
                'conversion' => $conversionName,
            ]);
        } catch (\Throwable $th) {
            // Fallback to default URL if an error occurs
        }
        return $this->model?->getUrl($conversionName);
    }
}
