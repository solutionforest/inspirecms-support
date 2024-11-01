<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Actions\Concerns;

use SolutionForest\InspireCms\Support\TreeNodes\Actions\ActionGroup;

trait BelongsToTreeNodeItem
{
    public null | string | int $itemKey = null;

    public function itemKey(null | int | string $itemKey): static
    {
        $this->itemKey = $itemKey;

        return $this;
    }

    public function getItemKey(): null | string | int
    {
        if ($this->itemKey) {
            return $this->itemKey;
        }
        
        if (($group = $this->getGroup()) instanceof ActionGroup) {
            return $group->getItemKey();
        }

        return null;
    }
}
