<?php

namespace SolutionForest\InspireCms\Support\Base\Models\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface CanPrunable
{
    /**
     * Get the prunable model query.
     *
     * @return Builder
     */
    public function prunable();
}
