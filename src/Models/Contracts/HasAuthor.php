<?php

namespace SolutionForest\InspireCms\Support\Models\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
     * @return MorphTo
     */
    public function author();
}
