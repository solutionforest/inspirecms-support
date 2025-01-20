<?php

namespace SolutionForest\InspireCms\Support\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string|int $id
 * @property string $nestable_type
 * @property string|int $nestable_id
 * @property int $order
 * @property string|int $parent_id
 * @property-read ?\DateTimeInterface $created_at
 * @property-read ?\DateTimeInterface $updated_at
 */
interface NestableTree
{
    /**
     * Get the nestable relationship for the nestable tree.
     *
     * @return MorphTo The nestable relationship.
     */
    public function nestable();

    /**
     * Sets a new order for nestable items.
     *
     * @param  array  $parentIds  The parent ID for NestabeleTree.
     * @param  array  $morphableIds  An array of item IDs to be reordered.
     * @param  string  $morphableType  The type of the morphable entity.
     * @return void
     */
    public static function setNewOrderForNestable($parentId, array $morphableIds, string $morphableType);

    /**
     * Rebuilds the tree structure for a nestable entity.
     *
     * @param string $morphableType The type of the morphable entity.
     * @param array $morphableIds An array of IDs for the morphable entities. Default is an empty array.
     * @return void
     */
    public static function rebuildTreeForNestable($morphableType, $morphableIds = []);
}
