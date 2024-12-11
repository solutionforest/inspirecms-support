<?php

namespace SolutionForest\InspireCms\Support\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $author_type
 * @property string $author_id
 * @property null|Model $author
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
