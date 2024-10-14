<?php

namespace SolutionForest\InspireCms\Support\Resolver;

interface UserResolverInterface
{
    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function resolve();
}
