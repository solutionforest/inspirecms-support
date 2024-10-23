<?php

namespace SolutionForest\InspireCms\Support\Base\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface BelongToNestableTree
{
    /**
     * Defines a relationship method for a nestable tree structure.
     *
     * This method should be implemented to establish a MorphOne relationship
     * that represents the nestable tree structure for the implementing model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function nestableTree(): MorphOne;
    /**
     * Interface method to retrieve tree data.
     *
     * @return array An array representing the tree data.
     */
    public function getTreeData(): array;
    /**
     * Get the ID of the parent node.
     *
     * This method returns the ID of the parent node in the nestable tree structure.
     *
     * @return string|int|null The ID of the parent node, or null if there is no parent.
     */
    public function getParentId(): string|int|null;
}
