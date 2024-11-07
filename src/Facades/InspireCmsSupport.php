<?php

namespace SolutionForest\InspireCms\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void setTablePrefix(string $tablePrefix)
 * @method static void setNestableTreeModel(string $model)
 * @method static string getTablePrefix()
 * @method static string getNestableTreeModel()
 *
 * @see \SolutionForest\InspireCms\Support\InspireCmsSupportManager
 */
class InspireCmsSupport extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \SolutionForest\InspireCms\Support\InspireCmsSupportManager::class;
    }
}
