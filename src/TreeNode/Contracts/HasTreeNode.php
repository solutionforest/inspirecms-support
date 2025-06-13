<?php

namespace SolutionForest\InspireCms\Support\TreeNode\Contracts;

interface HasTreeNode extends HasTreeNodeActions
{
    /**
     * @return TreeNode
     */
    public function getTreeNode();
}
