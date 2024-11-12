<?php

namespace SolutionForest\InspireCms\Support\Models\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NestableTreeDetailScope implements Scope
{
    public function apply($builder, Model $model)
    {
        $nestableTree = $model->nestableTree()->getRelated();

        $subQ = $nestableTree::query()->whereColumn('nestable_type', $model->getMorphClass());

        $q = $builder->getQuery();

        $q->leftJoinSub($subQ, 'nestable_tree', $model->getQualifiedKeyName(), '=', 'nestable_tree.nestable_id');

        if (is_null($q->columns) || empty($q->columns)) {
            $q->addSelect($model->qualifyColumn('*'));
        }
        $q->addSelect([
            'nestable_tree.' . ($nestableTree->determineOrderColumnName() ?? 'order') . ' as nestable_order',
            'nestable_tree.' . ($nestableTree->getNestableParentIdName() ?? 'parent_id') . ' as nestable_tree_parent_id',
            'nestable_tree.' . ($nestableTree->getKeyName() ?? 'id') . ' as nestable_tree_id',
        ]);
    }
}
