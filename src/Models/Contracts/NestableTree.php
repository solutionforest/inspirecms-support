<?php

namespace SolutionForest\InspireCms\Support\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\NestableInterface;
use Spatie\EloquentSortable\Sortable;

interface NestableTree extends NestableInterface, Sortable
{
    /**
     * Get the nestable relationship for the nestable tree.
     *
     * @return MorphTo The nestable relationship.
     */
    public function nestable(): MorphTo;

    /**
     * Sets a new order for nestable items.
     *
     * @param array $ids An array of item IDs to be reordered.
     * @param string $morphableType The type of the morphable entity.
     * @param int $startOrder The starting order value for the items. Default is 1.
     *
     * @return void
     */
    public static function setNewOrderForNestable($ids, string $morphableType, int $startOrder = 1);
}
