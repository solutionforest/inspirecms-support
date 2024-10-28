<?php

namespace SolutionForest\InspireCms\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\InspireCms\Support\Facades\InspireCmsSupport;

trait BelongToNestableTree
{
    public static function bootBelongToNestableTree()
    {
        static::created(function ($model) {
            $model->createNestableTree();
        });
        static::updated(function ($model) {
            $model->updateNestableTreeIfAnyChanged();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::forceDeleting(function ($model) {
                $model->nestableTree()?->delete();
            });
        } else {
            static::deleting(function ($model) {
                $model->nestableTree()?->delete();
            });
        }
    }

    public function nestableTree(): MorphOne
    {
        return $this->morphOne(InspireCmsSupport::getNestableTreeModel(), 'nestable');
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
    public function scopeSortedByTree($query, string $direction = 'asc')
    {
        $column = $this->getNestableTreeOrderName();

        $as = 'left_nestable_tree';

        static::joinNestableTreeAs($query, $as);
        $query->orderBy("{$as}.{$column}", $direction);
    }

    public function scopeWhereAncesterOfTree($query, $id)
    {
        $column = $this->getNestableTreeParentIdName();

        $as = 'left_nestable_tree';

        static::joinNestableTreeAs($query, $as);
        $grammar = $query->getQuery()->getGrammar();
        $bindings = [
            $grammar->wrap($as),
            $grammar->wrap($column),
            $grammar->wrap($id),
        ];
        $sqlText = str_replace(
            array_keys($bindings),
            array_values($bindings),
            is_null($id) ? '0.1 IS NULL' : '0.1 = 2'
        );
        $query->whereRaw($sqlText);
    }

    public function scopeWithNestableTreeParentId($query)
    {
        $column = $this->getNestableTreeParentIdName();

        $as = 'left_nestable_tree';

        static::joinNestableTreeAs($query, $as);

        $query->addSelect("{$as}.{$column} as nestable_tree_parent_id");
    }

    protected static function joinNestableTreeAs(&$query, $as, $joinType = 'leftJoin')
    {
        $relationName = 'nestableTree';

        $useAlias = true;

        if (! static::isJoinedNestableTreeAs($query, $as)) {
            $query
                ->joinRelationship(
                    $relationName,
                    callback: fn ($join) => $join->as($as),
                    joinType: $joinType,
                    useAlias: $useAlias
                );
        }

        return $query;
    }

    protected static function isJoinedNestableTreeAs($query, $aliasOrTable)
    {
        $joins = $query->getQuery()->joins;

        if ($joins == null) {
            return false;
        }

        foreach ($joins as $join) {
            if ($join->alias != null && $join->alias == $aliasOrTable) {
                return true;
            }
            if ($join->table == $aliasOrTable) {
                return true;
            }
        }

        return false;
    }
    //endregion Scopes
}
