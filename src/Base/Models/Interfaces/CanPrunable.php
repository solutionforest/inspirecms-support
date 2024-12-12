<?php

namespace SolutionForest\InspireCms\Support\Base\Models\Interfaces;

interface CanPrunable
{
    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable();
}
