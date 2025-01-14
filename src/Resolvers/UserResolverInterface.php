<?php

namespace SolutionForest\InspireCms\Support\Resolvers;

use SolutionForest\InspireCms\Support\Base\Resolvers\BaseResolverInterface;

/**
 * @extends BaseResolverInterface<\Illuminate\Contracts\Auth\Authenticatable>
 */
interface UserResolverInterface extends BaseResolverInterface
{
    /**
     * Resolve the user for the given model.
     *
     * @param  ?\Illuminate\Database\Eloquent\Model  $model  The model instance to resolve the user for.
     * @return ?\Illuminate\Contracts\Auth\Authenticatable The resolved user.
     */
    public function resolveForModel($model);
}
