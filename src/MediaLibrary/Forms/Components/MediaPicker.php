<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Support\Dtos\MediaAssetDto;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\Concerns\HasMediaFilterTypes;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\Concerns\LimitsMediaSelection;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Throwable;

class MediaPicker extends Field
{
    use HasMediaFilterTypes;
    use LimitsMediaSelection;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms-support::forms.components.media-picker';

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
                    // Situation 1: If the item is an array, get the 'uid' key (Normal)
                    // Situation 2: If the item is not an array, it is assumed to be a uid (string or int) (Preview)
                    ->map(fn ($item) => is_array($item) ? $item['uid'] ?? null : $item)
                    ->all();

                $state = $component->getCachedSelectedAssets($ids)->keys()->all();

            } catch (Throwable $th) {
                $state = [];
            }
            $component->rawState($state);
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

        $this->registerActions([
            fn (self $component): Action => $component->getSelectAction(),
            fn (self $component): Action => $component->getClearAction(),
        ]);
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

    public function clearCachedSelectedAssets(): void
    {
        $this->cachedSelectedAssets = null;
    }

    /**
     * @return Collection<Model>
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

    public function getSelectAction(): Action
    {
        return Action::make('select')
            ->label(__('inspirecms-support::media-library.buttons.select.label'))
            ->modalWidth(Width::Screen)
            ->fillForm(fn () => [
                'selection' => $this->getState() ?? [],
            ])
            ->extraModalWindowAttributes([
                'class' => 'media-library-browser-modal-content',
            ])
            ->modalHeading(__('inspirecms-support::media-library.buttons.select.heading'))
            ->modalSubmitActionLabel(__('inspirecms-support::media-library.buttons.select.label'))
            ->modalCancelActionLabel(__('inspirecms-support::media-library.buttons.cancel.label'))
            ->schema(function () {
                $selector = MediaSelect::make('selection')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->max(fn () => $this->getMax())
                    ->min(fn () => $this->getMin())
                    ->when($this->getFilterTypes(), fn (MediaSelect $component) => $component->filterTypes($this->getFilterTypes()));

                return [$selector];
            })
            ->action(function (array $arguments, array $data, MediaPicker $component) {
                $ids = $data['selection'] ?? [];

                $component->clearCachedSelectedAssets();

                $component->rawState($ids);

                $component->callAfterStateUpdatedHooks();
            });
    }

    public function getClearAction(): Action
    {
        return Action::make('clear')
            ->label(__('inspirecms-support::media-library.buttons.clear.label'))
            ->color('gray')
            ->action(function () {
                $this->state([]);
            });
    }

    // region Helpers

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

    private function getOrderedAssets(array $ids): Collection
    {
        return $this->getEloquentQuery()
            ->folders(false) // Filter out the folders
            ->findMany($ids)
            ->mapWithKeys(fn (Model $asset) => [$asset->getKey() => $asset])
            // Sort the assets by the order of the ids
            ->sortBy(fn ($asset, $key) => array_search($key, $ids));
    }

    // endregion Helpers
}
