<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

class ModelRegistry implements ModelRegistryInterface
{
    protected array $models = [];

    public function __construct()
    {
        $this->models = [
            \SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset::class => \SolutionForest\InspireCms\Support\Models\MediaAsset::class,
            \SolutionForest\InspireCms\Support\Models\Contracts\NestableTree::class => \SolutionForest\InspireCms\Support\Models\Polymorphic\NestableTree::class,
        ];
    }

    /** {@inheritDoc} */
    public function get(string $interfaceClass)
    {
        return $this->models[$interfaceClass] ?? null;
    }

    /** {@inheritDoc} */
    public function replace(string $interfaceClass, string $modelClass)
    {
        $this->models[$interfaceClass] = $modelClass;
    }
}
