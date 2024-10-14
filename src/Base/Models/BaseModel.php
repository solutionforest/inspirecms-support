<?php

namespace SolutionForest\InspireCms\Support\Base\Models;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Facades\InspireCmsSupport;

abstract class BaseModel extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(InspireCmsSupport::getTablePrefix() . $this->getTable());
    }
}
