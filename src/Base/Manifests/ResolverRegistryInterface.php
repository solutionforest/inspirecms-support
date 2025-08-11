<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

/**
 * @template T \SolutionForest\InspireCms\Support\Base\Resolvers\BaseResolverInterface
 */
interface ResolverRegistryInterface
{
    public function set(string $interface, string $resolver): void;

    /**
     * @return null | T
     */
    public function get(string $interface);
}
