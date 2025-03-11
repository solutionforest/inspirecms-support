<?php

namespace SolutionForest\InspireCms\Support\Base\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;

abstract class BasePivotModel extends Pivot
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(ModelRegistry::getTablePrefix() . $this->getTable());
    }
}
