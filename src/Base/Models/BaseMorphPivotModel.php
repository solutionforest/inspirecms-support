<?php

namespace SolutionForest\InspireCms\Support\Base\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;

abstract class BaseMorphPivotModel extends MorphPivot
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(ModelRegistry::getTablePrefix() . $this->getTable());
    }
}
