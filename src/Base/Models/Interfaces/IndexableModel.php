<?php

namespace SolutionForest\InspireCms\Support\Base\Models\Interfaces;

interface IndexableModel
{
    public function toSearchableArray(): array;
}
