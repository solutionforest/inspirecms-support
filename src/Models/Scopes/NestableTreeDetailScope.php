<?php

namespace SolutionForest\InspireCms\Support\Models\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use SolutionForest\InspireCms\Support\Models\Contracts\NestableTree;

class NestableTreeDetailScope implements Scope
{
    public function apply($builder, Model $model)
    {
        /**
         * @var Model & NestableTree $nestableTree
         */
        $nestableTree = $model->nestableTree()->getRelated();

        $subQ = $nestableTree::query()
            ->select([
                ($nestableTree->determineOrderColumnName() ?? 'order') . ' as nestable_tree_order',
                ($nestableTree->getParentIdName() ?? 'parent_id') . ' as nestable_tree_parent_id',
                ($nestableTree->getKeyName() ?? 'id') . ' as nestable_tree_id',
                'nestable_type as nestable_tree_type',
                'nestable_id as nestable_tree_nestable_id',
            ]);

        $q = $builder->getQuery()
            ->leftJoinSub($subQ, 'nestable_tree', $model->getKeyName(), '=', 'nestable_tree.nestable_tree_nestable_id')
            ->where('nestable_tree.nestable_tree_type', $model->getMorphClass());
    }
}
