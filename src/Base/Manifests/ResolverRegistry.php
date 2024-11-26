<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

class ResolverRegistry implements ResolverRegistryInterface
{
    protected array $resolvers = [];

    public function __construct()
    {
        $this->resolvers = [
            'user' => \SolutionForest\InspireCms\Support\Resolvers\UserResolver::class,
        ];
    }

    public function set(string $name, string $resolver): void
    {
        $this->resolvers[$name] = $resolver;
    }

    public function get(string $name): ?string
    {
        return $this->resolvers[$name] ?? null;
    }
}
