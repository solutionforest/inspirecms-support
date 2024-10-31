<?php

namespace SolutionForest\InspireCms\Support\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\BelongsToNestableTree;

class BelongsToNestableTreeObserver
{
    /**
     * Handle the created event.
     *
     * @param  \SolutionForest\InspireCms\Support\Base\Models\Interfaces\BelongsToNestableTree  $model
     * @return void
     */
    public function created(BelongsToNestableTree | Model $model)
    {
        $model->ensureNestableTree();
    }

    /**
     * Handle the updated event.
     *
     * @param  \SolutionForest\InspireCms\Support\Base\Models\Interfaces\BelongsToNestableTree  $model
     * @return void
     */
    public function updated(BelongsToNestableTree | Model $model)
    {
        $model->ensureNestableTree();
    }

    /**
     * Handle the deleting event.
     *
     * @param  \SolutionForest\InspireCms\Support\Base\Models\Interfaces\BelongsToNestableTree  $model
     * @return void
     */
    public function deleting(BelongsToNestableTree | Model $model)
    {
        if ($this->supportSoftDeletes($model)) {
            return;
        }

        $this->deleteNestableTree($model);
    }

    /**
     * Handle the forceDeleting event.
     *
     * @param  \SolutionForest\InspireCms\Support\Base\Models\Interfaces\BelongsToNestableTree  $model
     * @return void
     */
    public function forceDeleting(BelongsToNestableTree | Model $model)
    {
        $this->deleteNestableTree($model);
    }

    protected function supportSoftDeletes(BelongsToNestableTree | Model $model)
    {
        return in_array(SoftDeletes::class, class_uses($model));
    }

    protected function deleteNestableTree(BelongsToNestableTree | Model $model)
    {
        $model->nestableTree?->delete();
    }
}
