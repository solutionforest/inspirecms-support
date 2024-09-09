<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\FileExplorer\Concerns;

use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasFileExplorer;

trait BelongsToLivewire
{
    protected HasFileExplorer $livewire;

    public function livewire(HasFileExplorer $livewire): static
    {
        $this->livewire = $livewire;

        return $this;
    }

    public function getLivewire(): HasFileExplorer
    {
        return $this->livewire;
    }
}
