<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Concerns;

use Filament\Forms;
use Filament\Forms\Form;

trait HasSorts
{
    public array $sort = [];

    protected function fillsortForm(): void
    {
        $this->sortForm->fill($this->sort ?? []);
    }

    protected function getsortFormStatePath(): string
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
                    ->live(true)
                    ->default('default')
                    ->hidden(fn ($component) => $this->isSortColumnInvisible($component->getName()))
                    ->dehydratedWhenHidden(),

                Forms\Components\Select::make('direction')
                    ->hiddenLabel()
                    ->placeholder(__('inspirecms-support::media-library.sort.direction.placeholder'))
                    ->options(__('inspirecms-support::media-library.sort.direction.options'))
                    ->selectablePlaceholder(false)
                    ->live(true)
                    ->default('asc')
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
}
