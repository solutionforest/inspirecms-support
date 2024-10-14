<?php

namespace SolutionForest\InspireCms\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void setTablePrefix(string $tablePrefix)
 * @method static string getTablePrefix()
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
