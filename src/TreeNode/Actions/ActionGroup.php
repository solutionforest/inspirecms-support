<?php

namespace SolutionForest\InspireCms\Support\TreeNode\Actions;

use Filament\Actions\ActionGroup as BaseAction;
use SolutionForest\InspireCms\Support\TreeNode\Contracts\TreeNode;

class ActionGroup extends BaseAction
{
    use Concerns\BelongsToTreeNode;
    use Concerns\BelongsToTreeNodeItem;

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'itemKey' => [$this->getItemKey()],
            'treeNode' => [$this->getTreeNode()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        $treeNode = $this->getTreeNode();

        if (! $treeNode) {
            return parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType);
        }

        return match ($parameterType) {
            TreeNode::class => [$treeNode],
            default => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
        };
    }

    public function isHidden(): bool
    {
        if ($this->baseIsHidden()) {
            return true;
        }

        foreach ($this->getActions() as $action) {
            if ($action instanceof ActionGroup && $action->isHidden()) {
                continue;
            }

            if ($action->isHiddenInGroup()) {
                continue;
            }

            return false;
        }

        return true;
    }
}
