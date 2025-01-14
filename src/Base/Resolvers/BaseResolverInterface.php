<?php

namespace SolutionForest\InspireCms\Support\Base\Resolvers;

/**
 * @template T
 */
interface BaseResolverInterface
{
    /**
     * @return T|null
     */
    public function resolve(...$args);
}
