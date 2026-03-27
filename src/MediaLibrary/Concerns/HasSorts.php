<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Concerns;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property Schema $sortForm
 */
trait HasSorts
{
    public array $sort = [];

    public function mountHasSorts(): void
    {
        $this->fillSortForm($this->sort);
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

    public function sortForm(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1])
            ->dense()
            ->components([
                Select::make('type')
                    ->hiddenLabel()
                    ->placeholder(__('inspirecms-support::media-library.sort.type.placeholder'))
                    ->options(__('inspirecms-support::media-library.sort.type.options'))
                    ->selectablePlaceholder(false)
                    ->live()
                    ->disabled(fn ($component) => $this->isSortColumnDisabled($component->getName()))
                    ->dehydratedWhenHidden()
                    ->suffixActions([
                        Action::make('asc_sort')
                            ->label(__('inspirecms-support::media-library.sort.direction.options.asc'))
                            ->icon(Heroicon::ArrowUp)
                            // ->action(fn () => $this->sort['direction'] = 'asc')
                            // ->color(fn () => ($this->sort['direction'] ?? null) === 'asc' ? 'primary' : 'gray')
                            // ->color(fn ($get) => ($get('direction') ?? null) === 'asc' ? 'primary' : 'gray')
                            // ->after(fn () => $this->clearCache())
                            ->visible(function ($get) {
                                if ($this->isSortColumnDisabled('direction')) {
                                    return false;
                                }

                                return $get('direction') !== 'asc';
                            })
                            ->action(fn ($set) => $set('direction', 'asc'))
                            ->iconButton(),
                        Action::make('desc_sort')
                            ->label(__('inspirecms-support::media-library.sort.direction.options.desc'))
                            ->icon(Heroicon::ArrowDown)
                            // ->action(fn () => $this->sort['direction'] = 'desc')
                            // ->action(fn ($set) => $set('direction', 'desc'))
                            // ->color(fn ($get) => ($get('direction') ?? null) === 'desc' ? 'primary' : 'gray')
                            // ->color(fn () => ($this->sort['direction'] ?? null) === 'desc' ? 'primary' : 'gray')
                            // ->after(fn () => $this->clearCache())
                            ->visible(function ($get) {
                                if ($this->isSortColumnDisabled('direction')) {
                                    return false;
                                }

                                return $get('direction') === 'asc';
                            })
                            ->action(fn ($set) => $set('direction', 'desc'))
                            ->iconButton(),
                    ]),

                Hidden::make('direction')
                    ->live()
                    ->disabled(fn ($component) => $this->isSortColumnDisabled($component->getName()))
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
     * @param  Builder  $query  The query builder instance.
     * @return Builder
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
