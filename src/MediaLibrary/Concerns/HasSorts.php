<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Concerns;

use Filament\Forms;
use Filament\Forms\Form;

/**
 * @property Form $sortForm
 */
trait HasSorts
{
    public array $sort = [];

    public function mountHasSorts(): void
    {
        $this->fillSortForm();
    }

    protected function fillSortForm(array $data = []): void
    {
        $this->sortForm->fill($this->mutateSortData($data));
    }

    protected function mutateSortData(array $data): array
    {
        return $data;
    }

    protected function getSortFormStatePath(): string
    {
        return 'sort';
    }

    public function sortForm(Form $form): Form
    {
        return $form
            ->extraAttributes(['class' => 'gap-y-2 lg:gap-x-2'])
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('type')
                    ->hiddenLabel()
                    ->placeholder(__('inspirecms-support::media-library.sort.type.placeholder'))
                    ->options(__('inspirecms-support::media-library.sort.type.options'))
                    ->selectablePlaceholder(false)
                    ->live()
                    ->hidden(fn ($component) => $this->isSortColumnInvisible($component->getName()))
                    ->dehydratedWhenHidden(),

                Forms\Components\Select::make('direction')
                    ->hiddenLabel()
                    ->placeholder(__('inspirecms-support::media-library.sort.direction.placeholder'))
                    ->options(__('inspirecms-support::media-library.sort.direction.options'))
                    ->selectablePlaceholder(false)
                    ->live()
                    ->hidden(fn ($component) => $this->isSortColumnInvisible($component->getName()))
                    ->dehydratedWhenHidden(),
            ])
            ->statePath($this->getSortFormStatePath());
    }

    protected function ensureSort(): array
    {
        return array_filter(
            $this->sort,
            fn ($value): bool => (is_array($value) && ! empty($value)) ||
                (is_string($value) && strlen($value) > 0)
        );
    }

    /**
     * Apply sorting to the given query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySortCriteria($query)
    {
        $sort = $this->ensureSort();

        if (empty($sort)) {
            return $query;
        }
        $sortDirection = $sort['direction'] ?? 'asc';

        switch ($sort['type'] ?? null) {
            case 'name':
                $query->withAggregate('media', 'name')->orderBy('media_name', $sortDirection);

                break;
            case 'created_at':
                $query->withAggregate('media', 'created_at')->orderBy('media_created_at', $sortDirection);

                break;
            case 'updated_at':
                $query->withAggregate('media', 'updated_at')->orderBy('media_updated_at', $sortDirection);

                break;
            case 'size':
                $query->withSum('media', 'size')->orderBy('media_sum_size', $sortDirection);
            default:
                $query->orderBy('id', $sortDirection);

                break;
        }

        return $query;
    }
}
