<?php

namespace SolutionForest\InspireCms\Support\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class MediaBrowser extends Field
{
    use Concerns\HasMediaFilterTypes;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms-support::forms.components.media-picker.browser';

    protected array $filterTypes = [];

    protected bool $multiple = false;

    protected null | Closure | int | string $startNode = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (MediaBrowser $component, $state) {
            if ($component->isMultiple()) {
                if (! is_array($state)) {
                    $state = empty($state) ? [] : array_filter([$state]);
                }
            } else {
                if (is_array($state)) {
                    $state = $state[0] ?? null;
                }
            }

            $component->state($state);
        });

        $this->dehydrateStateUsing(function (MediaBrowser $component, $state) {
            if ($state && ! is_array($state)) {
                $state = [$state];
            }
            if (! empty($state)) {
                $state = array_filter($state);
                $media = $component->getMediaModelQuery()->findMany($state);

                // filter out folders
                $state = $media->filter(fn (MediaAsset | Model $m) => ! $m->isFolder())->map(fn (MediaAsset | Model $m) => $m->getKey())->toArray();
            }

            return $state ?? [];
        });

        $this->startNode(function (MediaBrowser $component, $state) {
            if ($component->isMultiple()) {
                return null; // root
            }

            $key = is_array($state) ? ($state[0] ?? null) : $state;
            if (blank($key) || empty($key)) {
                return null; // root
            }

            /**
             * @var null | MediaAsset | Model 
             */
            $media = $component->getMediaModelQuery()->find($key);
            if (! $media) {
                return null; // root
            }

            return $media->{$media->getParentKeyName()};
        });
    }

    public function multiple(bool $condition = true): static
    {
        $this->multiple = $condition;

        return $this;
    }

    public function startNode(Closure | string | int $startNode): static
    {
        $this->startNode = $startNode;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function getStartNode(): null | int | string
    {
        return $this->evaluate($this->startNode);
    }

    protected function getMediaModelQuery(): Builder
    {
        return static::getMediaModel()::query();
    }

    protected static function getMediaModel(): string
    {
        return ModelRegistry::get(MediaAsset::class);
    }
}
