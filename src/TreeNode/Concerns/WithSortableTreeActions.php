<?php

namespace SolutionForest\InspireCms\Support\TreeNode\Concerns;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

trait WithSortableTreeActions
{
    public function cacheWithSortableTreeActions()
    {
        foreach ([
            'toolbar' => method_exists($this, 'getToolbarActions') ? $this->getToolbarActions() : [],
            'nodeItem' => method_exists($this, 'getNodeItemActions') ? $this->getNodeItemActions() : [],
        ] as $type => $actions) {

            if (! is_array($actions)) {
                continue;
            }

            foreach ($actions as $action) {

                if ($action instanceof ActionGroup) {
                    foreach ($action->getFlatActions() as $subAction) {
                        $this->cacheAction($subAction);
                    }
                } elseif ($action instanceof Action) {
                    $this->cacheAction($action);
                }
            }
        }
    }
}
