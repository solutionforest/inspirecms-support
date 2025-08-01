<?php

namespace SolutionForest\InspireCms\Support\TreeNode\ModelExplorer\Concerns;

use Closure;

trait ModelExplorerBase
{
    protected string $model;

    protected string $parentColumnName = 'parent_id';

    protected null | int | string $rootLevelKey = null;

    protected ?Closure $determineRecordParentIdUsing = null;

    public function model(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function parentColumnName(string $parentColumnName): static
    {
        $this->parentColumnName = $parentColumnName;

        return $this;
    }

    public function rootLevelKey(null | int | string $rootLevelKey): static
    {
        $this->rootLevelKey = $rootLevelKey;

        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function determineRecordParentIdUsing(Closure $closure): static
    {
        $this->determineRecordParentIdUsing = $closure;

        return $this;
    }

    public function getParentColumnName(): string
    {
        return $this->parentColumnName;
    }

    public function getRootLevelKey(): null | int | string
    {
        return $this->rootLevelKey;
    }

    /**
     * @param string | int $parentKey
     * @param array<string,mixed> $items
     * @param array<string,mixed> $nodes
     * @return void
     */
    public function attachItemsToNodes(string | int $parentKey, array $items, array &$nodes)
    {
        foreach ($nodes as &$node) {
            if ($node['key'] === $parentKey) {
                $node['children'] = array_merge($node['children'] ?? [], $items);

                return;
            }
        }

        // search deeper
        foreach ($nodes as &$node) {
            if (!is_array($node['children']) || empty($node['children'])) {
                continue;
            }

            $this->attachItemsToNodes($parentKey, $items, $node['children']);
        }
    }
}
