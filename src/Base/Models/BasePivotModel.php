<?php

namespace SolutionForest\InspireCms\Support\Base\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use SolutionForest\InspireCms\Support\Facades\InspireCmsSupport;

abstract class BasePivotModel extends Pivot
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(InspireCmsSupport::getTablePrefix() . $this->getTable());
    }
}
