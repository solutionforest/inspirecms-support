<?php

namespace SolutionForest\InspireCms\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\Models\Scopes\NestableTreeDetailScope;
use SolutionForest\InspireCms\Support\Observers\BelongsToNestableTreeObserver;

trait BelongsToNestableTree
{
    public static function bootBelongsToNestableTree()
    {
        static::observe(new BelongsToNestableTreeObserver);
    }

    /**
     * @return MorphOne
     */
    public function nestableTree()
    {
        $model = ModelRegistry::get(\SolutionForest\InspireCms\Support\Models\Contracts\NestableTree::class);

        return $this->morphOne($model, 'nestable');
    }

    public function ensureNestableTree()
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
        if ($parent = $this->getParentNestableTree()) {
            $parent->appendNode($node);
        } else {
            $node->makeRoot()->save();
        }

    }

    protected function updateNestableTreeIfAnyChanged()
    {
        if (! $this->exists) {
            return;
        }

        $this->loadMissing('nestableTree');
        if ($node = $this->nestableTree) {
            // skip if not update
            if (! $this->isDirty($this->getParentKeyName())) {
                return;
            }

            $newParentNodeId = $this->getParentNestableTreeId();

            $node->{$node->getParentIdName()} = $newParentNodeId;
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
        return $this->parent?->nestableTree?->getKey() ?? $this->getNestableTreeRootLevelParentId();
    }

    public function getNestableTreeRootLevelParentId()
    {
        return $this->nestableTree()->getRelated()->getRootLevelParentId();
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
        return $this->nestableTree()->getRelated()->getParentIdName();
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
        return $this->nestableTree()->getRelated()->qualifyColumn($this->getParentId());
    }

    // region Scopes

    public function scopeWhereAncesterOfTree(Builder $query, $id)
    {
        // Ensure the nestable tree is loaded
        $query->withGlobalScope(NestableTreeDetailScope::class, new NestableTreeDetailScope);

        $query->where('nestable_tree_parent_id', $id);
    }

    public function scopeSortedByTree(Builder $query, string $direction = 'asc')
    {
        // Ensure the nestable tree is loaded
        $query->withGlobalScope(NestableTreeDetailScope::class, new NestableTreeDetailScope);

        $query->orderBy('nestable_tree_order', $direction);
    }
    // endregion Scopes

}
