<?php

namespace SolutionForest\InspireCms\Support\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Support\Base\Manifests\ModelRegistryInterface;

/**
 * @method static ?string get(string $intefaceClass)
 * @method static void replace(string $intefaceClass, string $modelClass)
 * @method static void setTablePrefix(string $tablePrefix)
 * @method static string getTablePrefix()
 *
 * @see \SolutionForest\InspireCms\Support\Base\Manifests\ModelRegistry
 */
class ModelRegistry extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return ModelRegistryInterface::class;
    }
}
