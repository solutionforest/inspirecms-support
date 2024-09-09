<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer\Concerns;

trait HasModelItems
{
    public function getRootItems()
    {
        $query = $this->getModelExplorerQuery();

        $rootKey = $this->getRootLevelKey();
        $parentColumnName = $this->getParentColumnName();

        if ($rootKey) {
            $query->where($parentColumnName, $rootKey);
        } else {
            $query->whereNull($parentColumnName);
        }

        return $query->get();
    }

    public function getChildren($parentKey)
    {
        $query = $this->getModelExplorerQuery();

        $parentColumnName = $this->getParentColumnName();

        $query->where($parentColumnName, $parentKey);

        return $query->get();
    }

    public function getModelExplorerQuery()
    {
        $model = $this->getModel();

        if (empty($model)) {
            throw new \Exception('Model not configured: Please set up the model for the ModelExplorer.');
        }

        return $model::query();
    }
}
