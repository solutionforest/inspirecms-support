<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

use Illuminate\Contracts\Foundation\Application;
use SolutionForest\InspireCms\Support\Resolvers\UserResolver;
use SolutionForest\InspireCms\Support\Resolvers\UserResolverInterface;

class ResolverRegistry implements ResolverRegistryInterface
{
    protected array $resolvers = [];

    public function __construct()
    {
        $this->resolvers = [
            UserResolverInterface::class => UserResolver::class,
        ];
    }

    public function register(Application $application): void
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
