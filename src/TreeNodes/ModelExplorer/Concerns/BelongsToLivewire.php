<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer\Concerns;

use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;

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
