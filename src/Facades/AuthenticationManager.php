<?php

namespace SolutionForest\InspireCms\Support\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Support\Base\Manifests\AuthManagerInterface;

/**
 * @method static void setAuthGuard(string $guard)
 * @method static string getAuthGuard()
 *
 * @see \SolutionForest\InspireCms\Support\Base\Manifests\AuthManager
 */
class AuthenticationManager extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return AuthManagerInterface::class;
    }
}
