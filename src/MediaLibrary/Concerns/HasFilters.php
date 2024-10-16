<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Concerns;

use Filament\Forms;
use Filament\Forms\Form;

trait HasFilters
{
    public array $filter = [];

    protected function fillFilterForm(): void
    {
        $this->filterForm->fill($this->filter ?? []);
    }

    protected function getFilterFormStatePath(): string
    {
        return 'filter';
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->columns(4)
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->hiddenLabel()
                    ->placeholder(__('inspirecms-support::media-library.filter.title.placeholder'))
                    ->live(true)
                    ->extraAttributes(fn (Forms\Components\Field $component) => [
                        'class' => $this->isFilterColumnInvisible($component->getName()) ? 'hidden' : null,
                    ]),
                Forms\Components\Select::make('type')
                    ->hiddenLabel()
                    ->placeholder(__('inspirecms-support::media-library.filter.type.placeholder'))
                    ->options(__('inspirecms-support::media-library.filter.type.options'))
                    ->multiple()
                    ->live(true)
                    ->extraAttributes(fn (Forms\Components\Field $component) => [
                        'class' => $this->isFilterColumnInvisible($component->getName()) ? 'hidden' : null,
                    ]),
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
}
