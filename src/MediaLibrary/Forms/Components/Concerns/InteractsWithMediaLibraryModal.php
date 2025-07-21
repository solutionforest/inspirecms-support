<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

trait InteractsWithMediaLibraryModal
{
    public function getMediaLibraryModalId(): string
    {
        return 'media-library-picker-modal';
    }

    public function getMediaLibraryModalConfig(array $filterTypes = []): array
    {
        return [
            'page' => 1,
            'forms' => [
                'filter' => [
                    'd' => ['type' => $filterTypes],
                    'disabledColumns' => ! empty($filterTypes) ? ['type'] : [],
                ],
                'sort' => [],
            ],
        ];
    }

    private function getFormComponentMediaPickerUrl(array | string $ids): array
    {
        if (! is_array($ids)) {
            $ids = [$ids];
        }

        $ids = collect($ids)->filter()->flatten()->all();

        /**
         * @var Collection<MediaAsset> $media
         */
        $media = InspireCmsConfig::getMediaAssetModelClass()::whereKey($ids)->get();

        return collect($media)
            ->keyBy(fn (Model $record) => $record->getKey())
            ->sortKeysUsing(fn ($a, $b) => array_search($a, $ids) <=> array_search($b, $ids))
            ->map(fn (MediaAsset $record) => $record->getUrl())
            ->filter()
            ->all();
    }
}
