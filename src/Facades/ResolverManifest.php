<?php

namespace SolutionForest\InspireCms\Support\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Support\Base\Manifests\ResolverManifestInterface;

/**
 * @method static void set(string $name, string $resolver)
 * @method static ?string get(string $name)
 *
 * @see \SolutionForest\InspireCms\Support\Base\Manifests\ResolverManifest
 */
class ResolverManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return ResolverManifestInterface::class;
    }
}
