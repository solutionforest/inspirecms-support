<?php

namespace SolutionForest\InspireCms\Support\Models\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $author_type
 * @property string $author_id
 * @property-read null|Model&Authenticatable $author
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
