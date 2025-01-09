<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

#[Lazy]
class MediaDetailComponent extends Component implements Contracts\HasItemActions
{
    use Concerns\HasItemActions;
    use Concerns\WithMediaAssets;

    #[Reactive]
    public array $selectedMediaId = [];

    #[Reactive]
    public ?string $toggleMediaId = null;

    #[Reactive]
    public bool $isModalPicker = false;

    public function placeholder()
    {
        return view('inspirecms-support::components.media-library.loading-section', [
            'count' => 1,
            'height' => '100dvh',
        ]);
    }

    public function render()
    {
        return view('inspirecms-support::livewire.components.media-library.media-detail', [
            'toggleMedia' => $this->resolveToggleMedia(),
        ]);
    }

    /**
     * @param null | (Model & MediaAsset) $asset
     * @return bool
     */
    public function canViewInformation($asset)
    {
        if ($this->isModalPicker) {

            return $asset != null;
        }
        
        return count($this->selectedMediaId) == 1 && $asset != null;
    }

    protected function resolveToggleMedia()
    {
        if ($this->toggleMediaId == null) {
            return null;
        }

        return $this->resolveAssetRecord($this->toggleMediaId);
    }

    protected function getFirstSelectedMedia()
    {
        if (count($this->selectedMediaId) != 1) {
            return null;
        }

        return $this->resolveAssetRecord(Arr::first($this->selectedMediaId));
    }

    /**
     * @param  Model & MediaAsset  $asset
     * @return array
     */
    public function getInformationFor($asset)
    {
        $media = $asset?->getFirstMedia();

        return collect($asset->getDisplayedColumns())
            ->map(function ($key) use ($media, $asset) {
                $fallback = match ($key) {
                    'created_at', 'updated_at' => trans(
                        "inspirecms-support::media-library.detail_info.{$key}.empty",
                    ),
                    default => '',
                };
                $customPropertyKey = str_replace('custom-property.', '', $key);
                $value = match ($key) {
                    'size' => ($asset->isFolder() ? '' : $media?->human_readable_size) ?? $fallback,
                    'created_at', 'updated_at' => ($asset->isFolder()
                        ? $asset?->{$key}->format('Y-m-d H:i:s')
                        : $media?->{$key}->format('Y-m-d H:i:s')) ?? $fallback,
                    'uploaded_by', 'created_by' => $mediaItem->author?->name ?? $fallback,
                    // Default for not custom properties
                    $customPropertyKey => ($asset->isFolder()
                        ? $asset?->{$key}
                        : $media?->{$key}) ?? $fallback,
                    // Default for custom properties
                    default => $media->getCustomProperty($customPropertyKey) ?? $fallback,
                };

                return [
                    'label' => trans("inspirecms-support::media-library.detail_info.{$key}.label"),
                    'value' => $value,
                ];
            })
            ->all();
    }

    // region Actions
    protected function getMediaItemActions(): array
    {
        return [
            Actions\BulkDeleteAction::make()
                ->after(fn () => $this->dispatch('resetMediaLibrary')),
        ];
    }
    // endregion Actions

    /**
     * @param  Collection<Model & MediaAsset>  $assets
     * @return array
     */
    protected function mututaThumbnail($assets)
    {
        if ($assets->count() != 1) {
            return [];
        }

        /**
         * @var null | Model | MediaAsset $asset
         */
        $asset = $assets->first();

        $data['is_image'] = $asset->isImage() ?? false;
        $data['thumbnail'] = $asset->isImage()
            ? $asset->getThumbnailUrl()
            : $asset->getThumbnail();

        return $data;
    }

    /**
     * @param  Collection<Model & MediaAsset>  $assets
     * @return array
     */
    protected function mutateInformation($assets)
    {
        if ($assets->count() != 1) {
            return [];
        }

        /**
         * @var null | Model | MediaAsset $asset
         */
        $asset = $assets->first();
        $media = $asset?->getFirstMedia();

        return collect($asset->getDisplayedColumns())
            ->map(function ($key) use ($media, $asset) {
                $fallback = match ($key) {
                    'created_at', 'updated_at' => trans(
                        "inspirecms-support::media-library.detail_info.{$key}.empty",
                    ),
                    default => '',
                };
                $customPropertyKey = str_replace('custom-property.', '', $key);
                $value = match ($key) {
                    'size' => ($asset->isFolder() ? '' : $media?->human_readable_size) ?? $fallback,
                    'created_at', 'updated_at' => ($asset->isFolder()
                        ? $asset?->{$key}->format('Y-m-d H:i:s')
                        : $media?->{$key}->format('Y-m-d H:i:s')) ?? $fallback,
                    'uploaded_by', 'created_by' => $mediaItem->author?->name ?? $fallback,
                    // Default for not custom properties
                    $customPropertyKey => ($asset->isFolder()
                        ? $asset?->{$key}
                        : $media?->{$key}) ?? $fallback,
                    // Default for custom properties
                    default => $media->getCustomProperty($customPropertyKey) ?? $fallback,
                };

                return [
                    'label' => trans("inspirecms-support::media-library.detail_info.{$key}.label"),
                    'value' => $value,
                ];
            })
            ->all();
    }
}
