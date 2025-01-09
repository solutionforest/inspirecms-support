<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Concerns;

use Filament\Forms;
use Filament\Forms\Form;
use SolutionForest\InspireCms\Support\MediaLibrary\FilterType;

/**
 * @property Form $filterForm
 */
trait HasFilters
{
    public array $filter = [];

    public function mountHasFilters(): void
    {
        $this->fillFilterForm();
    }

    protected function fillFilterForm(array $data = []): void
    {
        $this->filterForm->fill($this->mutateFilterData($data));
    }

    protected function mutateFilterData(array $data): array
    {
        return $data;
    }

    protected function getFilterFormStatePath(): string
    {
        return 'filter';
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->columns(['default' => 1, 'lg' => 2])
            ->extraAttributes(['class' => 'gap-y-2 lg:gap-x-2'])
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->hiddenLabel()
                    ->placeholder(__('inspirecms-support::media-library.filter.title.placeholder'))
                    ->live(false)
                    ->hidden(fn ($component) => $this->isFilterColumnInvisible($component->getName()))
                    ->dehydratedWhenHidden()
                    ->extraAttributes(['class' => 'w-full'])
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('clear')
                            ->label(__('inspirecms-support::media-library.actions.clear.label'))
                            ->color('gray')
                            ->icon('heroicon-o-x-mark')
                            ->action(fn ($component) => $component->state(''))
                            ->after(fn () => $this->clearCache())
                    ),
                Forms\Components\Select::make('type')
                    ->hiddenLabel()
                    ->placeholder(__('inspirecms-support::media-library.filter.type.placeholder'))
                    ->options(FilterType::class)
                    ->multiple()
                    ->live(true)
                    ->hidden(fn ($component) => $this->isFilterColumnInvisible($component->getName()))
                    ->dehydratedWhenHidden(),
            ])
            ->statePath($this->getFilterFormStatePath());
    }

    protected function ensureFilter(): array
    {
        return array_filter(
            $this->filter,
            fn ($value): bool => (is_array($value) && ! empty($value)) ||
            (is_string($value) && strlen($value) > 0)
        );
    }

    /**
     * Apply a filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query  The query builder instance.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFilterCriteria($query)
    {
        $filter = $this->ensureFilter();

        if (isset($filter['title'])) {
            $query->where('title', 'like', "%{$filter['title']}%");
            unset($filter['title']);
        }

        $query->where(function ($query) use ($filter) {
            if (count($filter) > 0) {
                $query
                    ->whereHas('media', function ($query) use ($filter) {

                        foreach ($filter as $key => $value) {
                            switch ($key) {
                                case 'type':
                                    if (is_array($value)) {
                                        $mimeTypes = collect(FilterType::toMimeTypes($value))
                                            ->filter()
                                            ->map(fn ($value) => str_replace('*', '%', $value))
                                            ->filter(fn ($value) => ! is_null($value) && $value != '%')
                                            ->toArray();
                                        $query = $query->where(function ($q) use ($mimeTypes) {
                                            foreach ($mimeTypes as $mimeType) {
                                                if (str_contains($mimeType, '%')) {
                                                    $q->orWhere('mime_type', 'like', $mimeType);
                                                } else {
                                                    $q->orWhere('mime_type', $mimeType);
                                                }
                                            }
                                        });
                                    }

                                    break;
                                default:
                                    if (! is_null($value)) {
                                        $query->where($key, $value);
                                    }

                                    break;
                            }
                        }
                    });

                if ($this->isModalPicker) {
                    $query->orWhereDoesntHave('media');
                }
            }
        });

        return $query;
    }
}
