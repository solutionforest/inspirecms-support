<?php

namespace SolutionForest\InspireCms\Support\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Support\Base\Manifests\ResolverRegistryInterface;
use SolutionForest\InspireCms\Support\Base\Resolvers\BaseResolverInterface;

/**
 * @method static void set(string $interface, string $resolver)
 * @method static null|BaseResolverInterface get(string $name)
 *
 * @see \SolutionForest\InspireCms\Support\Base\Manifests\ResolverRegistry
 */
class ResolverRegistry extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return ResolverRegistryInterface::class;
    }
}
