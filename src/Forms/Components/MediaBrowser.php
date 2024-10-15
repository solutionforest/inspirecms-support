<?php

namespace SolutionForest\InspireCms\Support\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryManifest;

class MediaBrowser extends Field
{
    /**
     * @var view-string
     */
    protected string $view = 'inspirecms-support::forms.components.media-picker.browser';

    protected array $mimeTypes = ['*'];

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
                $state = $media->filter(fn ($m) => ! $m->isFolder())->map(fn ($m) => $m->getKey())->toArray();
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

            $media = $component->getMediaModelQuery()->find($key);
            if (! $media) {
                return null; // root
            }

            return $media->{$media->getNestableParentIdColumn()};
        });
    }

    public function image(): static
    {
        return $this->mimeTypes(['image/*']);
    }

    public function mimeTypes(array $mimeTypes): static
    {
        $this->mimeTypes = $mimeTypes;

        return $this;
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

    public function getMimeTypes(): array
    {
        return $this->mimeTypes;
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
        return MediaLibraryManifest::getModel();
    }
}
