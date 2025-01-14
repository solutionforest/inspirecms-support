<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

class ResolverRegistry implements ResolverRegistryInterface
{
    protected array $resolvers = [];

    public function __construct()
    {
        $this->resolvers = [
            \SolutionForest\InspireCms\Support\Resolvers\UserResolverInterface::class => \SolutionForest\InspireCms\Support\Resolvers\UserResolver::class,
        ];
    }

    public function register(\Illuminate\Contracts\Foundation\Application $application): void
    {
        foreach ($this->resolvers as $interface => $resolver) {
            $application->scoped($interface, fn () => $application->make($resolver));
        }
    }

    public function set(string $interface, string $resolver): void
    {
        $this->resolvers[$interface] = $resolver;
    }

    public function get(string $interface)
    {
        return app($interface);
    }
}
