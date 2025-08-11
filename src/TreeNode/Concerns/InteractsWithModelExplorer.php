<?php

namespace SolutionForest\InspireCms\Support\TreeNode\Concerns;

use SolutionForest\InspireCms\Support\TreeNode\ModelExplorer;

/**
 * @property \Filament\Schemas\Schema $mountedTreeNodeItemActionSchema
 */
trait InteractsWithModelExplorer
{
    use CanSelectModeltem;

    protected ModelExplorer $modelExplorer;

    public function bootInteractsWithModelExplorer()
    {
        $this->modelExplorer = $this->modelExplorer($this->makeModelExplorer());
    }

    public function modelExplorer(ModelExplorer $modelExplorer): ModelExplorer
    {
        return $modelExplorer
            ->model($this->getModelExplorerModel())
            ->parentColumnName($this->getModelExplorerParentColumnName())
            ->rootLevelKey($this->getModelExplorerRootLevelKey());
    }

    public function getModelExplorer(): ModelExplorer
    {
        return $this->modelExplorer;
    }

    public function getTreeNode()
    {
        return $this->getModelExplorer();
    }

    protected function makeModelExplorer(): ModelExplorer
    {
        return ModelExplorer::make($this);
    }

    public function getModelExplorerModel(): string
    {
        return '';
    }

    public function getModelExplorerParentColumnName(): string
    {
        return '';
    }

    public function getModelExplorerRootLevelKey(): null | int | string
    {
        return null;
    }
}
