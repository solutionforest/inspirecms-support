<?php

namespace SolutionForest\InspireCms\Support\Base\Filament\Actions;

/**
 * Mixin class for adding tree node action group functionality to Filament components.
 *
 * This mixin provides common methods and properties for handling action groups
 * specifically designed for tree node structures in Filament admin panels.
 * It enables the creation and management of contextual actions that can be
 * applied to individual nodes within a tree hierarchy.
 *
 * @mixin \Filament\Actions\ActionGroup
 */
class TreeNodeActionGroupMixin
{
    public function recursiveTreeNodeAlpineId()
    {
        return function ($alpineNodeIdVariable) {

            if ($alpineNodeIdVariable && filled($alpineNodeIdVariable) && is_string($alpineNodeIdVariable)) {

                $actions = collect($this->getActions())->map(function ($action) use ($alpineNodeIdVariable) {
                    return $action->recursiveTreeNodeAlpineId($alpineNodeIdVariable);
                })->all();

                $this->actions($actions);
            }

            return $this;
        };
    }

    public function applyTreeNodeRecord()
    {
        return function ($record, $node = []) {

            $actions = collect($this->getActions())->map(function ($action) use ($record, $node) {
                return $action->applyTreeNodeRecord($record, $node);
            })->all();

            $this->actions($actions);

            return $this;
        };
    }
}
