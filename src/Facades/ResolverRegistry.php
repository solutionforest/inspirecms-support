<?php

namespace SolutionForest\InspireCms\Support\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Support\Base\Manifests\ResolverRegistryInterface;

/**
 * @method static void set(string $name, string $resolver)
 * @method static ?string get(string $name)
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
