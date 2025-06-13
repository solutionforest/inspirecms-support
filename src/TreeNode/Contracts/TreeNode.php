<?php

namespace SolutionForest\InspireCms\Support\TreeNode\Contracts;

interface TreeNode
{
    public function getNodeItemKey(array $item): mixed;
}
