<?php

namespace SolutionForest\InspireCms\Support\Dtos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseModelDto;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

/**
 * @extends BaseModelDto<MediaAsset|Model,MediaAssetDto>
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
     * @var ?string
     */
    public $src;

    /**
     * @var array<string,?string>
     */
    public array $responsive = [];

    public static function fromModel($model)
    {
        $media = $model->getFirstMedia();

        return parent::fromArray([
            'uid' => $model->getKey(),
            'caption' => $model->caption,
            'description' => $model->description,
            'meta' => $media?->manipulations,
            'src' => $model->getUrl(isAbsolute: false),
            'responsive' => collect($media?->generated_conversions ?? [])
                ->mapWithKeys(function ($condition, $conversion) use ($model) {
                    try {
                        $url = $model->getUrl($conversion, false);
                    } catch (\Throwable $e) {
                        $url = null;
                    }

                    return [$conversion => $url];
                })->all(),
        ])->setModel($model);
    }

    public function getUrl(string $conversionName = ''): ?string
    {
        $default = $this->src;
        if (filled($conversionName)) {
            return $this->responsive[$conversionName] ?? $default;
        }

        return $default;
    }

    /**
     * @param ...string $conversionNames
     */
    public function getSrcset(...$conversionNames): ?string
    {
        $srcset = [];

        foreach (Arr::flatten($conversionNames) as $conversionName) {
            if (($url = $this->getUrl($conversionName)) && filled($url)) {
                $srcset[] = $url;
            }
        }

        return implode(', ', $srcset);
    }

    public function getModel()
    {
        if ($this->model) {
            return $this->model;
        }

        if (! filled($this->uid)) {
            return null;
        }

        return $this->model = static::getMediaAssetModel()::with(['media'])->find($this->uid);
    }

    public function getFilename(): ?string
    {
        return $this->getModel()?->getFirstMedia()?->file_name;
    }

    public function getMimeType(): ?string
    {
        return $this->getModel()?->getFirstMedia()?->mime_type;
    }

    public function getSize(): ?int
    {
        return $this->getModel()?->getFirstMedia()?->size;
    }

    public function getExtension(): ?string
    {
        return $this->getModel()?->getFirstMedia()?->extension;
    }

    protected static function getMediaAssetModel(): string
    {
        return ModelRegistry::get(MediaAsset::class);
    }
}
