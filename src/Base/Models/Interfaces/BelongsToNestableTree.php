<?php

namespace SolutionForest\InspireCms\Support\Base\Models\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface BelongsToNestableTree
{
    /**
     * Defines a relationship method for a nestable tree structure.
     *
     * This method should be implemented to establish a MorphOne relationship
     * that represents the nestable tree structure for the implementing model.
     */
    public function nestableTree(): MorphOne;

    public function ensureNestableTree(): void;
    
    /**
     * @return ?Model
     */
    public function getParentNestableTree();

    /**
     * @return string|int|null
     */
    public function getParentNestableTreeId();
}
