<?php

namespace SolutionForest\InspireCms\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use SolutionForest\InspireCms\Support\Facades\InspireCmsSupport;
use SolutionForest\InspireCms\Support\Models\Scopes\NestableTreeDetailScope;
use SolutionForest\InspireCms\Support\Observers\BelongsToNestableTreeObserver;

trait BelongsToNestableTree
{
    public static function bootBelongsToNestableTree()
    {
        static::observe(new BelongsToNestableTreeObserver);

        static::addGlobalScope(new NestableTreeDetailScope);
    }

    public function nestableTree(): MorphOne
    {
        return $this->morphOne(InspireCmsSupport::getNestableTreeModel(), 'nestable');
    }

    public function ensureNestableTree(): void
    {
        if ($this->exists) {
            $this->updateNestableTreeIfAnyChanged();
        } else {
            $this->createNestableTree();
        }
    }

    protected function createNestableTree()
    {
        if (! $this->exists) {
            return;
        }

        $this->loadMissing('nestableTree');

        // already exists
        if ($node = $this->nestableTree) {
            return;
        }

        $node = $this->nestableTree()->make();
        $node->setParentNode($this->getParentNestableTree());

    }

    protected function updateNestableTreeIfAnyChanged()
    {
        if (! $this->exists) {
            return;
        }

        $this->loadMissing('nestableTree');
        if ($node = $this->nestableTree) {
            // skip if not update
            if (! $this->isDirty($this->getNestableParentIdName())) {
                return;
            }

            $newParentNodeId = $this->getParentNestableTreeId();
            $node->setParentNode($newParentNodeId, false);
            $node->moveToEnd();
            $node->{$node->determineOrderColumnName()}++;
            $node->save();
        }
        // if not create
        else {
            $this->createNestableTree();
        }
    }

    /**
     * @return ?Model
     */
    public function getParentNestableTree()
    {
        return $this->parent?->nestableTree;
    }

    /**
     * @return string|int|null
     */
    public function getParentNestableTreeId()
    {
        $fallbackParentId = $this->nestableTree()->getRelated()->getFallbackParentId();

        return $this->parent?->nestableTree?->getKey() ?? $fallbackParentId;
    }

    /**
     * @return string
     */
    public function getNestableTreeOrderName()
    {
        return $this->nestableTree()->getRelated()->determineOrderColumnName();
    }

    /**
     * @return string
     */
    public function getNestableTreeParentIdName()
    {
        return $this->nestableTree()->getRelated()->getNestableParentIdName();
    }

    /**
     * @return string
     */
    public function getQualifiedNestableTreeOrderName()
    {
        return $this->nestableTree()->getRelated()->qualifyColumn($this->getNestableTreeOrderName());
    }

    /**
     * @return string
     */
    public function getQualifiedNestableTreeParentIdName()
    {
        return $this->nestableTree()->getRelated()->qualifyColumn($this->getNestableTreeParentIdName());
    }

    //region Scopes

    public function scopeWhereAncesterOfTree(Builder $query, $id)
    {
        // Ensure the nestable tree is loaded
        $query->withGlobalScope(NestableTreeDetailScope::class, new NestableTreeDetailScope);

        $query->whereColumn('nestable_tree_parent_id', $id);
    }

    public function scopeSortedByTree(Builder $query, string $direction = 'asc')
    {
        // Ensure the nestable tree is loaded
        $query->withGlobalScope(NestableTreeDetailScope::class, new NestableTreeDetailScope);
        
        $query->orderBy('nestable_order', $direction);
    }
    //endregion Scopes

}
