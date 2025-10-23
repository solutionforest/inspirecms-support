<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Livewire;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\BulkDeleteAction;
use SolutionForest\InspireCms\Support\MediaLibrary\Concerns\HasItemActions as HasItemActionsTrait;
use SolutionForest\InspireCms\Support\MediaLibrary\Concerns\HasItemBulkActions;
use SolutionForest\InspireCms\Support\MediaLibrary\Concerns\WithMediaAssets;
use SolutionForest\InspireCms\Support\MediaLibrary\Contracts\HasItemActions;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

#[Lazy]
class MediaDetailComponent extends Component implements HasItemActions, HasItemBulkActions, HasSchemas
{
    use HasItemActionsTrait;
    use InteractsWithSchemas;
    use WithMediaAssets;

    #[Reactive]
    public array $selectedMediaId = [];

    #[Reactive]
    public ?string $toggleMediaId = null;

    #[Reactive]
    public bool $isModalPicker = false;

    public ?Model $mediaDetailRecord = null;

    public function placeholder()
    {
        return view('inspirecms-support::components.media-library.loading-section', [
            'count' => 1,
            'height' => '100dvh',
        ]);
    }

    public function render()
    {
        return view('inspirecms-support::livewire.components.media-library.media-detail');
    }

    public function hydrateToggleMediaId($value)
    {
        $this->mediaDetailRecord = $this->resolveToggleMedia();
    }

    /**
     * @param  null | (Model & MediaAsset)  $asset
     * @return bool
     */
    public function canViewInformation($asset)
    {
        if ($this->isModalPicker) {

            return $asset != null;
        }

        return count($this->getSelectedMediaAssetIds()) == 1 && $asset != null;
    }

    public function mediaDetailInfolist(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1])
            ->inlineLabel()
            ->dense()
            ->schema(function ($state) {
                $components = [];
                if (($asset = $this->mediaDetailRecord) && ($state = $this->getInformationFor($asset))) {
                    foreach ($state as $key => $value) {

                        $entryLabel = trans("inspirecms-support::media-library.detail_info.{$key}.label");

                        switch ($key) {
                            case 'created_at':
                            case 'updated_at':
                                $components[] = TextEntry::make($key)
                                    ->label($entryLabel)
                                    ->state($value)
                                    ->dateTime('Y-m-d H:i:s')
                                    ->fontFamily('mono')
                                    ->placeholder(__('inspirecms-support::media-library.detail_info.' . $key . '.empty'));

                                break;
                            default:
                                $components[] = TextEntry::make($key)
                                    ->label($entryLabel)
                                    ->state($value)
                                    ->fontFamily('mono')
                                    ->copyable(match ($key) {
                                        'model_id', 'size', 'uploaded_by', 'created_by' => true,
                                        default => false,
                                    })
                                    ->placeholder(match ($key) {
                                        'uploaded_by', 'created_by' => 'System',
                                        default => null,
                                    });

                                break;
                        }
                    }
                }

                return $components;
            });
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
        if (count($this->getSelectedMediaAssetIds()) != 1) {
            return null;
        }

        return $this->resolveAssetRecord(Arr::first($this->getSelectedMediaAssetIds()));
    }

    public function getSelectedMediaAssets(): Collection
    {
        return $this->resolveAssetRecords($this->getSelectedMediaAssetIds());
    }

    public function getSelectedMediaAssetIds(): array
    {
        return $this->selectedMediaId;
    }

    // region Actions
    protected function getMediaItemActions(): array
    {
        return [
            BulkDeleteAction::make()
                ->after(fn () => $this->dispatch('resetMediaLibrary')),
        ];
    }
    // endregion Actions

    /**
     * @param  Model & MediaAsset  $asset
     * @return array
     */
    protected function getInformationFor($asset)
    {
        $media = $asset?->getFirstMedia();

        return collect($asset->getDisplayedColumns())
            ->mapWithKeys(function ($key) use ($media, $asset) {
                $customPropertyKey = str_replace('custom-property.', '', $key);
                $value = match ($key) {
                    'size' => (! $asset->isFolder() ? $media?->human_readable_size : null),
                    'uploaded_by', 'created_by' => $asset->uploaded_by ?? null,
                    // Default for not custom properties
                    'created_at', 'updated_at', $customPropertyKey => ($asset->isFolder()
                        ? $asset?->{$key}
                        : $media?->{$key}) ?? null,
                    // Default for custom properties
                    default => $media->getCustomProperty($customPropertyKey) ?? null,
                };

                return [$key => $value];
            })
            ->all();
    }
}
