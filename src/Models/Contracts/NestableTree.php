<?php

namespace SolutionForest\InspireCms\Support\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface;
use Spatie\EloquentSortable\Sortable;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends HasRecursiveRelationshipsInterface<TModel>
 */
interface NestableTree extends HasRecursiveRelationshipsInterface, Sortable
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
}
