<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer\Concerns;

trait CanSelectItem
{
    protected ?int $maxSelectItem = null;

    protected ?int $minSelectItem = null;

    public function maxSelectItem(?int $max): static
    {
        $this->maxSelectItem = $max;

        return $this;
    }

    public function getMaxSelectItem(): ?int
    {
        return $this->maxSelectItem;
    }

    public function minSelectItem(?int $min): static
    {
        $this->minSelectItem = $min;

        return $this;
    }

    public function getMinSelectItem(): ?int
    {
        return $this->minSelectItem;
    }
}
