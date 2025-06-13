<?php

namespace SolutionForest\InspireCms\Support\TreeNode\Actions\Concerns;

use SolutionForest\InspireCms\Support\TreeNode\Actions\ActionGroup;
use SolutionForest\InspireCms\Support\TreeNode\Contracts\TreeNode;

trait BelongsToTreeNode
{
    protected TreeNode $treeNode;

    public function treeNode(TreeNode $treeNode): static
    {
        $this->treeNode = $treeNode;

        return $this;
    }

    public function getTreeNode(): ?TreeNode
    {
        if (isset($this->treeNode)) {
            return $this->treeNode;
        }

        $group = $this->getGroup();

        if ($group && ! ($group instanceof ActionGroup)) {
            throw new \Exception('This action does not belong to a tree node.');
        }

        return $group?->getTreeNode();
    }
}
