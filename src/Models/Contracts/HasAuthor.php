<?php

namespace SolutionForest\InspireCms\Support\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @property ?Model $author
 * @property string $author_type
 * @property string $author_id
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
