<?php

namespace SolutionForest\InspireCms\Support\TreeNode\Contracts;

use SolutionForest\InspireCms\Support\TreeNode\FileExplorer;

interface HasFileExplorer extends HasTreeNode
{
    public function fileExplorer(FileExplorer $fileExplorer): FileExplorer;

    public function getFileExplorer(): FileExplorer;

    public function getSelectedFileItemPath(): ?string;
}
