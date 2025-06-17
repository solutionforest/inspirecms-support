<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Support\Dtos\MediaAssetDto;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class MediaPicker extends Field
{
    use Concerns\HasMediaFilterTypes;
    use Concerns\InteractsWithMediaLibraryModal;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms-support::forms.components.media-picker.index';

    protected int | Closure | null $max = null;

    protected int | Closure | null $min = null;

    protected null | int | Closure $limitDisplay = null;

    /**
     * @var Collection<Model>|null
     */
    public $cachedSelectedAssets = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(function (MediaPicker $component, $state) {
            try {

                if (! is_array($state)) {
                    if ((is_string($state) && filled($state) && str($state)->isJson())) {
                        $state = json_decode($state, true);
                    } else {
                        // If state is not an array or string, convert it to an array
                        $state = [$state];
                    }
                }

                $ids = collect($state)
                    ->filter()
                    ->values()
                    ->pluck('uid')
                    ->all();

                $state = $component->getCachedSelectedAssets($ids)->keys()->all();

            } catch (\Throwable $th) {
                $state = [];
            }
            $component->state($state);
        });
        $this->afterStateUpdated(function (MediaPicker $component) {
            $component->clearCachedSelectedAssets();
        });
        // Ensure stored state as specified array format
        $this->mutateDehydratedStateUsing(function (MediaPicker $component, $state) {
            // Ensure the state is always an array
            if (! is_array($state)) {
                $state = is_null($state) || empty($state) ? [] : [$state];
            }
            $keys = array_values(array_unique(array_filter($state)));
            // find sorted media assets by keys
            $mediaAssets = $component->getOrderedAssets($keys);

            // Store as custom array format
            $result = collect($mediaAssets)
                ->map(fn (MediaAsset $asset) => collect(MediaAssetDto::fromModel($asset)?->toArray() ?? [])
                    ->forget('model')
                    ->all())
                ->values()->all();

            return $result;
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

    public function getLimitDisplay(): ?int
    {
        return $this->evaluate($this->limitDisplay);
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

        return $this->cachedSelectedAssets = $this->getOrderedAssets($ids);
    }

    private function getOrderedAssets(array $ids): Collection
    {
        return $this->getEloquentQuery()
            ->folders(false) // Filter out the folders
            ->findMany($ids)
            ->mapWithKeys(fn (Model $asset) => [$asset->getKey() => $asset])
            // Sort the assets by the order of the ids
            ->sortBy(fn ($asset, $key) => array_search($key, $ids));
    }
}
