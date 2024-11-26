<?php

namespace SolutionForest\InspireCms\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void setTablePrefix(string $tablePrefix)
 * @method static void setAuthGuard(string $guard)
 * @method static string getTablePrefix()
 * @method static string getAuthGuard()
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
