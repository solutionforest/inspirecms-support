<?php

namespace SolutionForest\InspireCms\Support\Base\Models\Interfaces;

/**
 * @see \Laravel\Scout\Searchable
 */
interface IndexableModel
{
    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray();
    
    /**
     * Make the given model instance searchable.
     *
     * @return void
     */
    public function searchable();
}
