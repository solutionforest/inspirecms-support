<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer;

trait InteractsWithModelExplorer
{
    use CanSelectModeltem;

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

    protected function configureSelectedModelItemFormAction(Action | ActionGroup $action): void
    {
        //
    }
}
