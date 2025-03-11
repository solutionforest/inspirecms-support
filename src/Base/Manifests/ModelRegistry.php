<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

use SolutionForest\InspireCms\Support\Models\Contracts as ModelContracts;
use SolutionForest\InspireCms\Support\Models\MediaAsset;
use SolutionForest\InspireCms\Support\Models\Polymorphic\NestableTree;

class ModelRegistry implements ModelRegistryInterface
{
    protected array $models = [];

    protected string $tablePrefix = '';

    public function __construct()
    {
        $this->models = [
            ModelContracts\MediaAsset::class => MediaAsset::class,
            ModelContracts\NestableTree::class => NestableTree::class,
        ];
    }

    /** {@inheritDoc} */
    public function get(string $interfaceClass): mixed
    {
        return $this->models[$interfaceClass] ?? null;
    }

    /** {@inheritDoc} */
    public function replace(string $interfaceClass, string $modelClass)
    {
        $this->models[$interfaceClass] = $modelClass;
    }

    public function setTablePrefix(string $tablePrefix): void
    {
        $this->tablePrefix = $tablePrefix;
    }

    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }
}
