<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Livewire\Attributes\On;
use Nette\NotImplementedException;

/**
 * @property Form $selectedModelItemForm
 */
trait CanSelectModeltem
{
    public array $modelExplorerSelectedItemData = [];

    #[On('getChildren')]
    public function getChildren($parentKey)
    {
        $children = $this->getModelExplorer()->getChildren($parentKey);

        $this->dispatch('childrenLoaded', children: $children->toArray());
    }

    #[On('selectItem')]
    public function selectItem($item)
    {
        //

    }

    public function getSelectedModelItemForm(): Form
    {
        if ((! $this->isCachingForms) && $this->hasCachedForm('selectedModelItemForm')) {
            return $this->getForm('selectedModelItemForm');
        }

        return $this->selectedModelItemForm(
            $this
                ->makeForm()
                ->schema($this->getSelectedModelItemFormSchema())
        )->statePath('modelExplorerSelectedItemData');
    }

    protected function getSelectedModelItemFormSchema(): array
    {
        if ($schema = $this->getModelExplorer()->getSelectedModelItemFormSchema()) {
            return $schema;
        }

        return [
            Forms\Components\TextInput::make('id')->readOnly(),
        ];
    }

    public function selectedModelItemForm(Form $form): Form
    {
        return $form;
    }

    public function getSelectedModelItemFormActions(): array
    {
        return $this->getModelExplorer()->getSelectedModelItemFormActions();
    }

    protected function configureSelectedModelItemFormAction(Action $action): void
    {
        //
    }

    public function saveSelectedModelItem()
    {
        throw new NotImplementedException('Please implement your ' . __FUNCTION__ . ' function.');
    }
}
