<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Contracts;

interface HasTreeNode extends HasTreeNodeActions
{
    /**
     * @return TreeNode
     */
    public function getTreeNode();
}
