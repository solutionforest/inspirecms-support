<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Nette\NotImplementedException;

/**
 * @property Form $selectedModelItemForm
 */
trait CanSelectModeltem
{
    public array $modelExplorerSelectedItemData = [];

    public array $cachedModelExplorerItems = [];

    public string | int $selectedModelItemKey = '';

    public ?Model $selectedModelItem = null;

    #[On('getNodes')]
    public function getModelExplorerNodes(string | int $parentKey, int $depth = 0)
    {
        if (isset($this->cachedModelExplorerItems[$parentKey])) {
            $items = $this->cachedModelExplorerItems[$parentKey];
        } else {
            $records = $this->getModelExplorer()->getRecordsFrom($parentKey);

            $items = $this->getModelExplorer()->parseAsItems($records, $depth)->toArray();

            $this->cachedModelExplorerItems[$parentKey] = $items;

        }
    }

    #[On('selectItem')]
    public function selectModelExplorerNode(string | int $nodeKey)
    {
        $this->selectedModelItemKey = $nodeKey;

        $this->refreshSelectedModelItem($nodeKey);
    }

    protected function mutateFileExplorerSelectedItemDataToFill(?Model $record): array
    {
        return $record?->attributesToArray() ?? [];
    }

    protected function resolveSelectedModelItem(string | int $key): Model
    {
        return $this->getModelExplorer()->findRecord($key);
    }

    protected function refreshSelectedModelItem(string | int $key): void
    {
        $this->selectedModelItem = $this->resolveSelectedModelItem($key);

        $this->selectedModelItemForm->model($this->selectedModelItem);

        $this->selectedModelItemForm->fill($this->mutateFileExplorerSelectedItemDataToFill($this->selectedModelItem));
    }

    public function getSelectedModelItem(): ?Model
    {
        return $this->selectedModelItem;
    }

    public function getGroupedNodeItems()
    {
        $modelExplorer = $this->getModelExplorer();

        if (empty($this->cachedModelExplorerItems)) {
            $this->getModelExplorerNodes($modelExplorer->getRootLevelKey());
        }

        // Convert the items array as node tree items array
        $nodes = [];
        $groupByDepth = collect($this->cachedModelExplorerItems)->flatten(1)->groupBy('depth');
        foreach ($groupByDepth as $depth => $flattenItems) {
            if ($depth === 0) {
                $nodes = collect($flattenItems)->map(fn ($item) => array_merge($item, ['children' => []]))->toArray();

                continue;
            }

            $groupByParentKey = collect($flattenItems)->groupBy('parentKey')->toArray();
            foreach ($groupByParentKey as $parentKey => $items) {
                $modelExplorer->attachItemsToNodes($parentKey, $items, $nodes);
            }

        }

        return $nodes;
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
