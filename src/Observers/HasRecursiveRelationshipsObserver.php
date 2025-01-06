<?php

namespace SolutionForest\InspireCms\Support\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface;

class HasRecursiveRelationshipsObserver
{
    /**
     * Handle the creating event.
     *
     * @param  \SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface  $model
     * @return void
     */
    public function creating(HasRecursiveRelationshipsInterface | Model $model)
    {
        // region Set the parent ID to the fallback parent ID if it is blank
        if (blank($model->{$model->getParentKeyName()}) && ! is_null($model->getRootLevelParentId())) {
            $model->{$model->getParentKeyName()} = $model->getRootLevelParentId();
        }
        // endregion
    }

    /**
     * Handle the deleting event.
     *
     * @param  \SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface  $model
     * @return void
     */
    public function deleting(HasRecursiveRelationshipsInterface | Model $model)
    {
        $model->children()->each(function ($child) {
            $child->delete();
        });
    }

    /**
     * Handle the forceDeleting event.
     *
     * @param  \SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface  $model
     * @return void
     */
    public function forceDeleting(HasRecursiveRelationshipsInterface | Model $model)
    {
        $model->children()->withTrashed()->each(function ($child) {
            $child->forceDelete();
        });
    }

    /**
     * Handle the restoring event.
     *
     * @param  \SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface  $model
     * @return void
     */
    public function restoring(HasRecursiveRelationshipsInterface | Model $model)
    {
        // To ensure that the parent fires the restoring event
        $parent = $model->parent()->withTrashed()->first();
        $parent?->restore();
    }
}
