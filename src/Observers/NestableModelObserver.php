<?php

namespace SolutionForest\InspireCms\Support\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\NestableInterface;

class NestableModelObserver
{
    /**
     * Handle the creating event.
     *
     * @param  \SolutionForest\InspireCms\Support\Base\Models\Interfaces\NestableInterface  $model
     * @return void
     */
    public function creating(NestableInterface | Model $model)
    {
        //region Set the parent ID to the fallback parent ID if it is blank
        if (blank($model->{$model->getNestableParentIdName()}) && ! is_null($model->getNestableRootValue())) {
            $model->{$model->getNestableParentIdName()} = $model->getNestableRootValue();
        }
        //endregion
    }

    /**
     * Handle the deleting event.
     *
     * @param  \SolutionForest\InspireCms\Support\Base\Models\Interfaces\NestableInterface  $model
     * @return void
     */
    public function deleting(NestableInterface | Model $model)
    {
        $model->children()->each(function ($child) {
            $child->delete();
        });
    }

    /**
     * Handle the forceDeleting event.
     *
     * @param  \SolutionForest\InspireCms\Support\Base\Models\Interfaces\NestableInterface  $model
     * @return void
     */
    public function forceDeleting(NestableInterface | Model $model)
    {
        $model->children()->withTrashed()->each(function ($child) {
            $child->forceDelete();
        });
    }

    /**
     * Handle the restoring event.
     *
     * @param  \SolutionForest\InspireCms\Support\Base\Models\Interfaces\NestableInterface  $model
     * @return void
     */
    public function restoring(NestableInterface | Model $model)
    {
        // To ensure that the parent fires the restoring event
        $parent = $model->parent()->withTrashed()->first();
        $parent?->restore();
    }
}
