<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Filament\Actions\Action;
use Filament\Forms;
use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer;

/**
 * @property Forms\Form $mountedTreeNodeItemActionForm
 */
trait InteractsWithModelExplorer
{
    use CanSelectModeltem;
    use HasTreeNodeItemActions;

    protected ModelExplorer $modelExplorer;

    public function bootInteractsWithModelExplorer()
    {
        $this->modelExplorer = Action::configureUsing(
            \Closure::fromCallable([$this, 'configureSelectedModelItemFormAction']),
            fn () => $this->modelExplorer($this->makeModelExplorer())
        );
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

    //region Forms
    /**
     * @return array<string, Forms\Form>
     */
    protected function getInteractsWithModelExplorerForms(): array
    {
        return [
            'mountedTreeNodeItemActionForm' => $this->getMountedTreeNodeItemActionForm(),
        ];
    }
    //endregion Forms
}
