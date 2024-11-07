<?php

namespace SolutionForest\InspireCms\Support;

class InspireCmsSupportManager
{
    protected string $tablePrefix;

    protected string $nestableTreeModel;

    public function __construct()
    {
        $this->tablePrefix = '';
        $this->nestableTreeModel = Models\Polymorphic\NestableTree::class;
    }

    public function setTablePrefix(string $tablePrefix): void
    {
        $this->tablePrefix = $tablePrefix;
    }

    public function setNestableTreeModel(string $model): void
    {
        $this->nestableTreeModel = $model;
    }

    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    public function getNestableTreeModel(): string
    {
        return $this->nestableTreeModel;
    }
}
