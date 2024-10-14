<?php

namespace SolutionForest\InspireCms\Support;

class InspireCmsSupportManager
{
    protected string $tablePrefix;

    public function setTablePrefix(string $tablePrefix): void
    {
        $this->tablePrefix = $tablePrefix;
    }

    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }
}
