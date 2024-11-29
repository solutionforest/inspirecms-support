<?php

namespace SolutionForest\InspireCms\Support\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @property ?Model $author
 */
interface HasAuthor
{
    /**
     * Get the author associated with the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function author();
}
