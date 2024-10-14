<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

interface ResolverManifestInterface
{
    public function set(string $name, string $resolver): void;

    public function get(string $name): ?string;
}
