<?php

namespace SolutionForest\InspireCms\Support\Base;

use Illuminate\Database\Migrations\Migration;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;

abstract class BaseMigration extends Migration
{
    protected ?string $prefix = null;

    public function __construct()
    {
        $this->prefix = ModelRegistry::getTablePrefix();
    }
}
