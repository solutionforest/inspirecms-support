<?php

namespace SolutionForest\InspireCms\Support\Base\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;

abstract class BaseAuthenticatableModel extends Authenticatable
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(ModelRegistry::getTablePrefix() . $this->getTable());
    }
}
