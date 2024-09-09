<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer\Concerns;

trait ModelExplorerBase
{
    protected string $model;

    protected string $parentColumnName = 'parent_id';

    protected null | int | string $rootLevelKey = null;

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

    public function getParentColumnName(): string
    {
        return $this->parentColumnName;
    }

    public function getRootLevelKey(): null | int | string
    {
        return $this->rootLevelKey;
    }
}
