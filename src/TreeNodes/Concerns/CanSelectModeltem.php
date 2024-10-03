<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

trait CanSelectModeltem
{
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
        $this->selectedModelItem($nodeKey);

        $this->refreshSelectedModelItem($nodeKey);
    }

    protected function resolveSelectedModelItem(string | int $key): Model
    {
        return $this->getModelExplorer()->findRecord($key);
    }

    protected function refreshSelectedModelItem(string | int $key): void
    {
        //
    }

    public function selectedModelItem(int | string | Model $record): static
    {
        if ($record instanceof Model) {
            $this->selectedModelItem = $record;
            $this->selectedModelItemKey = $record->getKey();
        } else {
            $this->selectedModelItemKey = $record;
            $this->selectedModelItem = $this->resolveSelectedModelItem($record);
        }

        return $this;
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
}
