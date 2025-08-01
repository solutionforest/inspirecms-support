<?php

namespace SolutionForest\InspireCms\Support\TreeNode\Actions;

use Filament\Actions\Action as BaseAction;
use Illuminate\Support\Js;
use SolutionForest\InspireCms\Support\TreeNode\Contracts\TreeNode;

class Action extends BaseAction
{
    use Concerns\BelongsToTreeNode;
    use Concerns\BelongsToTreeNodeItem;

    public function getLivewireCallMountedActionName(): string
    {
        return 'callMountedTreeNodeItemAction';
    }

    public function getLivewireEventClickHandler(): ?string
    {
        return $this->generateJavaScriptClickHandler('mountTreeNodeItemAction') ?? parent::getLivewireClickHandler();
    }

    protected function generateJavaScriptClickHandler(string $method): ?string
    {
        $itemKey = $this->getItemKey();

        $treeNode = $this->getTreeNode();

        $argumentsParameter = '';

        if (count($arguments = $this->getArguments())) {
            $argumentsParameter .= ', ';
            $argumentsParameter .= Js::from($arguments);
        }

        if (filled($itemKey) && $treeNode) {

            return "{$method}('{$this->getName()}', '{$itemKey}' {$argumentsParameter})";
        }

        return "{$method}('{$this->getName()}', '' {$argumentsParameter})";
    }

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
