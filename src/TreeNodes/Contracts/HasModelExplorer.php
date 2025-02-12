<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Contracts;

use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer;

interface HasModelExplorer extends HasTreeNode
{
    public function modelExplorer(ModelExplorer $modelExplorer): ModelExplorer;

    public function getModelExplorer(): ModelExplorer;

    public function cacheModelItemNode(string | int $parentKey, array $node): void;

    public function getCacheModelItemNode(string | int $key): ?array;

    public function getMaxSelectItem(): ?int;

    public function getMinSelectItem(): ?int;
}
