<?php

namespace SolutionForest\InspireCms\Support\TreeNode\ModelExplorer\Concerns;

use SolutionForest\InspireCms\Support\TreeNode\Contracts\HasModelExplorer;

trait BelongsToLivewire
{
    protected HasModelExplorer $livewire;

    public function livewire(HasModelExplorer $livewire): static
    {
        $this->livewire = $livewire;

        return $this;
    }

    public function getLivewire(): HasModelExplorer
    {
        return $this->livewire;
    }
}
