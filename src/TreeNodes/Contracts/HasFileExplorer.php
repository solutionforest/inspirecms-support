<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Contracts;

use SolutionForest\InspireCms\Support\TreeNodes\FileExplorer;

interface HasFileExplorer
{
    public function fileExplorer(FileExplorer $fileExplorer): FileExplorer;

    public function getSelectedFileItemPath(): ?string;
}
