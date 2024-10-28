<?php

namespace SolutionForest\InspireCms\Support\Base\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

interface NestableInterface
{
    /**
     * Get the parent relationship.
     */
    public function parent(): BelongsTo;

    public function children(): HasMany;

    /**
     * Get the descendants of the current entity.
     *
     * This method should return a collection of all descendant entities
     * related to the current entity, allowing for hierarchical data
     * retrieval.
     *
     * @return Collection A collection of descendant entities.
     */
    public function descendants(): Collection;

    /**
     * Get the ancestors of the current entity.
     *
     * This method should return a collection of all ancestor entities
     * related to the current entity, allowing for hierarchical data
     * retrieval.
     *
     * @return Collection A collection of ancestor entities.
     */
    public function ancestors(): Collection;

    /**
     * Get the level of the current entity in the hierarchy.
     *
     * This method returns an integer representing the depth of the
     * current entity within its parent-child structure.
     *
     * @return int The level of the current entity.
     */
    public function getLevel(): int;

    /**
     * Determine if the current entity is a root entity.
     *
     * This method returns a boolean indicating whether the current
     * entity has no parent, thus making it a root in the hierarchy.
     *
     * @return bool True if the entity is a root, false otherwise.
     */
    public function isRoot(): bool;

    /**
     * Determine if the current entity is a leaf entity.
     *
     * This method returns a boolean indicating whether the current
     * entity has no children, thus making it a leaf in the hierarchy.
     *
     * @return bool True if the entity is a leaf, false otherwise.
     */
    public function isLeaf(): bool;

    /**
     * Get the name of 'parent id' column.
     */
    public function getNestableParentIdName(): string;

    /**
     * Get the fully qualified column name for the nestable parent ID.
     *
     * @return string The fully qualified column name for the nestable parent ID.
     */
    public function getQualifiedNestableParentIdColumn(): string;

    /**
     * Get the root level parent_id for the nestable structure.
     *
     * This method returns the root value which can be either an integer or a string.
     *
     * @return int|string The root value for the nestable structure.
     */
    public function getNestableRootValue(): int | string | null;

    /**
     * Get the ID of the parent.
     *
     * @return int|string|null The ID of the parent, which can be an integer, a string, or null if there is no parent.
     */
    public function getParentId(): int | string | null;

    public function getFallbackParentId(): int | string | null;

    /**
     * Set the current instance as the root node.
     *
     * @param  bool  $save  Indicates whether to save the instance after setting it as root. Default is true.
     * @return void
     */
    public function asRoot($save = true);

    /**
     * Sets the parent node for the current node.
     *
     * @param  Model|string|int|null  $parent  The parent node to set.
     * @param  bool  $save  Whether to save the changes immediately. Default is true.
     * @return void
     */
    public function setParentNode($parent, $save = true);
}
