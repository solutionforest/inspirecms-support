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
            ->columns(2)
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
                    ),
                Forms\Components\Select::make('type')
                    ->hiddenLabel()
                    ->placeholder(__('inspirecms-support::media-library.filter.type.placeholder'))
                    ->options(__('inspirecms-support::media-library.filter.type.options'))
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
}
