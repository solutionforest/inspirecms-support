<?php

namespace SolutionForest\InspireCms\Support\Dtos;

use Illuminate\Support\Arr;
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
        $url = null;
        if (filled($conversionName)) {
            $media = $this->model->getFirstMedia();
            $url = $media?->getUrl($conversionName);
        }

        if (filled($url)) {
            return $url;
        }

        try {
            return route('inspirecms.asset', [
                'key' => $this->model?->getKey(),
            ]);
        } catch (\Throwable $th) {
            // Fallback to default URL if an error occurs
        }

        return $this->model?->getUrl($conversionName);
    }

    /**
     * @param ...string $conversionNames
     */
    public function getSrcset(...$conversionNames): ?string
    {
        $srcset = [];

        $media = $this->model?->getFirstMedia();
        if (! $media) {
            return null;
        }
        foreach (Arr::flatten($conversionNames) as $conversionName) {
            $url = $media->getSrcset($conversionName);
            if (filled($url)) {
                $srcset[] = $url;
            }
        }

        return implode(', ', $srcset);
    }
}
