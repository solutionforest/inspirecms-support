<?php

namespace SolutionForest\InspireCms\Support\Models\Scopes;

use Illuminate\Database\Eloquent\Scope;

class SortedScope implements Scope
{
    public function apply($builder, $model)
    {
        $column = method_exists($model, 'determineOrderColumnName') ? $model->determineOrderColumnName() : 'order'; 
        $builder->orderBy($column);
    }
}
