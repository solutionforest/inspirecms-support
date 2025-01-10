<?php

namespace SolutionForest\InspireCms\Support\Models\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NestableTreeDetailScope implements Scope
{
    public function apply($builder, Model $model)
    {
        $nestableTree = $model->nestableTree()->getRelated();

        $subQ = $nestableTree::query()
            ->whereColumn('nestable_tree_type', $model->getMorphClass())
            ->select([
                ($nestableTree->determineOrderColumnName() ?? 'order') . ' as nestable_tree_order',
                ($nestableTree->getParentKeyName() ?? 'parent_id') . ' as nestable_tree_parent_id',
                ($nestableTree->getKeyName() ?? 'id') . ' as nestable_tree_id',
                'nestable_type as nestable_tree_type',
                'nestable_id as nestable_tree_nestable_id',
            ]);

        $q = $builder->getQuery();

        $q->leftJoinSub($subQ, 'nestable_tree', $model->getQualifiedKeyName(), '=', 'nestable_tree_nestable_id');
    }
}
