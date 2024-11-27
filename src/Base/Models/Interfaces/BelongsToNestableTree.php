<?php

namespace SolutionForest\InspireCms\Support\Base\Models\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @see \SolutionForest\InspireCms\Support\Models\Concerns\BelongsToNestableTree
 */
interface BelongsToNestableTree
{
    /**
     * Defines a relationship method for a nestable tree structure.
     *
     * This method should be implemented to establish a MorphOne relationship
     * that represents the nestable tree structure for the implementing model.
     * 
     * @return MorphOne
     */
    public function nestableTree();

    /**
     * @return void
     */
    public function ensureNestableTree();

    /**
     * @return ?Model
     */
    public function getParentNestableTree();

    /**
     * @return string|int|null
     */
    public function getParentNestableTreeId();

    /**
     * @return string
     */
    public function getNestableTreeOrderName();

    /**
     * @return string
     */
    public function getNestableTreeParentIdName();

    /**
     * @return string
     */
    public function getQualifiedNestableTreeOrderName();

    /**
     * @return string
     */
    public function getQualifiedNestableTreeParentIdName();
}
