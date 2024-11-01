<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Actions;

use Filament\Actions\ActionGroup as BaseAction;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\TreeNode;

class ActionGroup extends BaseAction
{
    use Concerns\BelongsToTreeNodeItem;
    use Concerns\BelongsToTreeNode;

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
}
