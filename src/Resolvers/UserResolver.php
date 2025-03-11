<?php

namespace SolutionForest\InspireCms\Support\Resolvers;

use Illuminate\Support\Facades\Auth;
use SolutionForest\InspireCms\Support\Facades\AuthenticationManager;

class UserResolver implements UserResolverInterface
{
    /** {@inheritDoc} */
    public function resolve(...$args)
    {
        return $this->resolveForModel(null);
    }

    /** {@inheritDoc} */
    public function resolveForModel($model)
    {
        $guard = AuthenticationManager::getAuthGuard();

        try {
            $authenticated = Auth::guard($guard)->check();
        } catch (\Exception $exception) {
            return null;
        }

        if ($authenticated === true) {
            return Auth::guard($guard)->user();
        }

        return null;
    }
}
