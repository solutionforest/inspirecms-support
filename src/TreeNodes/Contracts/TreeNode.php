<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Contracts;

interface TreeNode
{
    public function getNodeItemKey(array $item): mixed;
}
