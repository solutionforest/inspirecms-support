<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer\Concerns;

use Illuminate\Database\Eloquent\Model;

trait CanSelectFileItem
{
    protected ?Model $selectedItem = null;

    public function selectedItem(Model $item): static
    {
        $this->selectedItem = $item;

        return $this;
    }

    public function getSelectedItem(): ?Model
    {
        return $this->selectedItem;
    }
}
