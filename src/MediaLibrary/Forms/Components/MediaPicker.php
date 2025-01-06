<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class MediaPicker extends Field
{
    use Concerns\HasMediaFilterTypes;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms-support::forms.components.media-picker.index';

    protected int | Closure | null $max = null;

    protected int | Closure | null $min = null;

    protected int | Closure $limitDisplay = 3;

    /**
     * @var Collection<Model>|null
     */
    public $cachedSelectedAssets = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(function (MediaPicker $component, $state) {
            $state = $component->getCachedSelectedAssets($state)->keys()->all();
            $component->state($state);
        });
        $this->afterStateUpdated(function (MediaPicker $component) {
            $component->clearCachedSelectedAssets();
        });

        $this->registerListeners([
            'mediaPicker::clearSelected' => [
                function (MediaPicker $component, string $statePath) {
                    if ($statePath === $component->getStatePath()) {
                        $component->state([]);
                    }
                },
            ],
            'mediaPicker::select' => [
                function (MediaPicker $component, string $statePath, $assetIds) {
                    if ($statePath === $component->getStatePath()) {
                        $state = $component->getCachedSelectedAssets($assetIds)->keys()->all();
                        $component->state($state);
                    }
                },
            ],
        ]);
    }

    public function max(int | Closure | null $max): static
    {
        $this->max = $max;

        $this->rule('array');
        $this->rule(static function (MediaPicker $component): string {
            $max = $component->getMax();

            return "max:{$max}";
        });

        return $this;
    }

    public function getMax(): ?int
    {
        return $this->evaluate($this->max);
    }

    public function min(int | Closure | null $min): static
    {
        $this->min = $min;
        $this->rule('array');
        $this->rule(static function (MediaPicker $component): string {
            $min = $component->getMin();

            return "min:{$min}";
        });

        return $this;
    }

    public function getMin(): ?int
    {
        return $this->evaluate($this->min);
    }

    public function limitDisplay(int | Closure $limit): static
    {
        $this->limitDisplay = $limit;

        return $this;
    }

    public function getLimitDisplay(): int
    {
        return $this->evaluate($this->limitDisplay);
    }

    public function getModalId(): string
    {
        return 'media-library-picker-modal';
    }

    /**
     * @return Builder
     */
    protected function getEloquentQuery()
    {
        return static::getMediaAssetModel()::query()->with('media');
    }

    /**
     * @return class-string<Model>
     */
    protected static function getMediaAssetModel(): string
    {
        return ModelRegistry::get(MediaAsset::class);
    }

    public function clearCachedSelectedAssets(): void
    {
        $this->cachedSelectedAssets = null;
    }

    /**
     * @return \Illuminate\Support\Collection<Model>
     */
    public function getCachedSelectedAssets($ids = null): Collection
    {
        if (! is_null($this->cachedSelectedAssets)) {
            return $this->cachedSelectedAssets;
        }

        $ids ??= $this->getState();

        if (! is_array($ids)) {
            $ids = is_null($ids) || empty($ids) ? [] : [$ids];
        }

        $ids = array_values(array_unique(array_filter($ids)));

        if (($max = $this->getMax()) != null) {
            $ids = array_slice($ids, 0, $max);
        }

        if (empty($ids)) {
            return $this->cachedSelectedAssets = collect();
        }

        return $this->cachedSelectedAssets = $this->getEloquentQuery()
            // Filter out the folders
            ->folders(false)
            ->findMany($ids)
            ->mapWithKeys(fn (Model $asset) => [$asset->getKey() => $asset])
            // Sort the assets by the order of the ids
            ->sortBy(fn ($asset, $key) => array_search($key, $ids));
    }
}
