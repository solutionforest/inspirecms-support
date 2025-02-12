<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait CanSelectModeltem
{
    public array $cachedModelExplorerItems = [];

    public array $selectedModelItemKeys = [];

    public array $expandedModelItemKeys = [];

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

    public function getMaxSelectItem(): ?int
    {
        return $this->getModelExplorer()->getMaxSelectItem();
    }

    public function getMinSelectItem(): ?int
    {
        return $this->getModelExplorer()->getMinSelectItem();
    }

    public function updatingExpandedModelItemKeys($value, $key = null)
    {
        if (is_array($value)) {
            foreach ($value as $recordKey) {
                $this->cacheModelExplorerNodesOn($recordKey);
            }
        }
    }

    public function updatingSelectedModelItemKeys($value, $key = null)
    {
        if (is_array($value)) {
            $this->expandParentModelItemIfSelected($value);
        }
    }

    protected function cacheModelExplorerNodesOn($parentKey)
    {
        $this->cacheModelItemNode($parentKey, $this->getModelExplorerItemsFrom($parentKey));
    }

    /**
     * @return Collection<Model>
     */
    protected function resolveSelectedModelItems(... $keys)
    {
        $keys = collect($keys)->flatten()->unique()->filter()->values()->all();
        
        return $this->getModelExplorer()->findRecord($keys);
    }

    public function getGroupedNodeItems()
    {
        $modelExplorer = $this->getModelExplorer();
        $rootLevelKey = $modelExplorer->getRootLevelKey();

        if (empty($this->cachedModelExplorerItems) || ! isset($this->cachedModelExplorerItems[$rootLevelKey]) || empty($this->cachedModelExplorerItems[$rootLevelKey])) {
            $this->cacheModelExplorerNodesOn(parentKey: $rootLevelKey);
        }

        // Convert the items array as node tree items array
        $nodes = [];
        $groupByDepth = collect($this->mutateCachedModelExplorerItemsBeforeGroup($this->cachedModelExplorerItems))->flatten(1)->groupBy('depth')->sortKeys();
        foreach ($groupByDepth as $depth => $flattenItems) {
            if ($depth === -1) {

                $nodes = collect($flattenItems)->map(fn ($item) => array_merge($item, ['children' => []]))->toArray();

                continue;
            } elseif ($depth === 0) {

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

    protected function mutateCachedModelExplorerItemsBeforeGroup(array $items): array
    {
        return $items;
    }

    protected function getModelExplorerItemsFrom(string | int $parentKey): array
    {
        if (isset($this->cachedModelExplorerItems[$parentKey])) {
            return $this->cachedModelExplorerItems[$parentKey];
        }

        $modelExplorer = $this->getModelExplorer();

        $records = $modelExplorer->getRecordsFrom($parentKey);

        $items = $this->mutuateModelExplorerNodes($records, $parentKey);

        if ($parentKey === $modelExplorer->getRootLevelKey()) {
            $items = $modelExplorer->mutuateRootNodeItems($items);
        }

        return $items;
    }

    /**
     * @param  Collection<Model>  $records
     */
    protected function mutuateModelExplorerNodes($records, string | int $parentKey): array
    {
        $modelExplorer = $this->getModelExplorer();

        return $modelExplorer->parseAsItems($records, $parentKey)->toArray();
    }

    protected function setSelectedModelItem(array $keys, bool $merge = true, bool $replace = false): void
    {
        $filteredKeys = $this->mutuateSelectedKeys($keys);
        
        if ($replace) {
            $this->selectedModelItemKeys = $filteredKeys;
        } elseif ($merge) {
            $this->selectedModelItemKeys = array_unique(array_merge($this->selectedModelItemKeys, $filteredKeys));
        } else {
            $this->selectedModelItemKeys = $filteredKeys;
        }
    }

    protected function setExpandedModelItem(array $keys, bool $merge = true, bool $replace = false): void
    {
        if ($replace) {
            $this->expandedModelItemKeys = $keys;
        } elseif ($merge) {
            $this->expandedModelItemKeys = array_unique(array_merge($this->expandedModelItemKeys, $keys));
        } else {
            $this->expandedModelItemKeys = $keys;
        }
    }

    protected function mutuateSelectedKeys(array $keys)
    {
        $max = $this->getMaxSelectItem();
        
        $filtered = collect($keys)
            ->unique()
            ->filter(fn ($key) => $this->isValidSelectableModelItemKey($key))
            ->values()
            ->all();

        if ($max != null && $max > 0) {
            $filtered = array_slice($filtered, 0, $max);
        }

        return $filtered;
    }

    protected function isValidSelectableModelItemKey($key): bool
    {
        if (is_array($key)) {
            return collect($key)
                ->flatten()
                ->unique()
                ->filter(fn ($key) => $this->isValidSelectableModelItemKey($key))
                ->isNotEmpty();
        }

        if (is_string($key)) {
            return filled($key) &&
                $key != null &&
                $key != intval('');
        }

        if (is_int($key)) {
            return $key != 0;
        }

        return false;
    }

    protected function getAncestorsFor(... $keys): array
    {
        return [];
    }

    protected function expandParentModelItemIfSelected(array $keys)
    {
        $ancestors = collect($this->getAncestorsFor($keys))
            ->flatten()
            ->keyBy(fn ($model) => $model->getKey())
            ->all();

        $this->setExpandedModelItem(keys: array_keys($ancestors), merge: true, replace: false);

        foreach (array_keys($ancestors) as $key) {
            $this->cacheModelExplorerNodesOn(parentKey: $key);
        }
    }
}
