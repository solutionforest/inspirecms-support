<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Contracts;

use SolutionForest\InspireCms\Support\TreeNodes\FileExplorer;

interface HasFileExplorer extends HasTreeNode
{
    public function fileExplorer(FileExplorer $fileExplorer): FileExplorer;

    public function getFileExplorer(): FileExplorer;

    public function getSelectedFileItemPath(): ?string;
}
