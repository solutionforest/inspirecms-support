<?php

namespace SolutionForest\InspireCms\Support\Resolvers;

use Illuminate\Support\Facades\Auth;

class UserResolver implements UserResolverInterface
{
    /** {@inheritDoc} */
    public function resolve(...$args)
    {
        return Auth::user();
    }
}
