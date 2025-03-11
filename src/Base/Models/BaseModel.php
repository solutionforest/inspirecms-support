<?php

namespace SolutionForest\InspireCms\Support\Base\Models;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;

abstract class BaseModel extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(ModelRegistry::getTablePrefix() . $this->getTable());
    }
}
