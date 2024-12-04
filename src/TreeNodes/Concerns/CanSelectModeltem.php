<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Egulias\EmailValidator\Result\Reason\EmptyReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Attributes\On;

trait CanSelectModeltem
{
    public array $cachedModelExplorerItems = [];

    public string | int $selectedModelItemKey = '';

    public ?Model $selectedModelItem = null;

    public function cacheModelItemNode(string | int $parentKey, array $node): void
    {
        if (! isset($this->cachedModelExplorerItems[$parentKey])) {
            $this->cachedModelExplorerItems[$parentKey] = $node;
        }
    }

    public function getCacheModelItemNode(string | int $key): ?array
    {
        $items = Arr::flatten($this->cachedModelExplorerItems, 1);

        foreach ($items as $item) {

            $checkKey = $this->getModelExplorer()->getNodeItemKey($item);

            if ($checkKey === $key) {
                return $item;
            }
        }

        return null;
    }

    #[On('getNodes')]
    public function cacheModelExplorerNodesOn(string | int $parentKey, int $depth = 0)
    {
        $this->cacheModelItemNode($parentKey, $this->getModelExplorerItemsFrom($parentKey, $depth));
    }

    #[On('selectItem')]
    public function selectModelExplorerNode(string | int | null $nodeKey)
    {
        $this->selectedModelItem($nodeKey);

        $this->refreshSelectedModelItem($nodeKey);
    }

    protected function resolveSelectedModelItem(string | int $key): ?Model
    {
        return $this->getModelExplorer()->findRecord($key);
    }

    protected function refreshSelectedModelItem(string | int | null $key): void
    {
        //
    }

    public function selectedModelItem(int | string | Model | null $record): static
    {
        $this->setSelectedModelItem($record);

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
            $this->cacheModelExplorerNodesOn($modelExplorer->getRootLevelKey());
        }

        // Convert the items array as node tree items array
        $nodes = [];
        $groupByDepth = collect($this->cachedModelExplorerItems)->flatten(1)->groupBy('depth')->sortKeys();
        foreach ($groupByDepth as $depth => $flattenItems) {
            if ($depth === -1) {

                $nodes = collect($flattenItems)->map(fn ($item) => array_merge($item, ['children' => []]))->toArray();

                continue;
            } else if ($depth === 0) {

                $nodesForRoot = collect($flattenItems)->map(fn ($item) => array_merge($item, ['children' => []]))->toArray();

                if (empty($nodes)) {

                    $nodes = $nodesForRoot;

                } else {
                        
                    $nodes = array_merge($nodes, $nodesForRoot);
                }

                continue;
            }  

            $groupByParentKey = collect($flattenItems)->groupBy('parentKey')->toArray();
            foreach ($groupByParentKey as $parentKey => $items) {
                $modelExplorer->attachItemsToNodes($parentKey, $items, $nodes);
            }

        }

        return $nodes;
    }

    protected function getModelExplorerItemsFrom(string | int $parentKey, int $depth): array
    {
        if (isset($this->cachedModelExplorerItems[$parentKey])) {
            return $this->cachedModelExplorerItems[$parentKey];
        }

        $modelExplorer = $this->getModelExplorer();

        $records = $modelExplorer->getRecordsFrom($parentKey);

        $items = $modelExplorer->parseAsItems($records, $depth)->toArray();

        if ($parentKey === $modelExplorer->getRootLevelKey()) {
            $items = $modelExplorer->mutuateRootNodeItems($items);
        }

        return $items;
    }

    protected function setSelectedModelItem(string | int | Model | null $record): void
    {
        if (is_null($record)) {
            $this->selectedModelItemKey = '';
            $this->selectedModelItem = null;
        } elseif ($record instanceof Model) {
            $this->selectedModelItem = $record;
            $this->selectedModelItemKey = $record->getKey();
        } else {
            $this->selectedModelItem = $this->resolveSelectedModelItem($record);
            $this->selectedModelItemKey = $this->selectedModelItem?->getKey() ?? '';
        }
    }
}
