<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Contracts;

use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer;

interface HasModelExplorer 
{
    public function modelExplorer(ModelExplorer $modelExplorer): ModelExplorer;
}
