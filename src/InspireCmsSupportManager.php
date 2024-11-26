<?php

namespace SolutionForest\InspireCms\Support;

class InspireCmsSupportManager
{
    protected string $tablePrefix = '';

    protected string $authGuard = 'web';

    public function setTablePrefix(string $tablePrefix): void
    {
        $this->tablePrefix = $tablePrefix;
    }

    public function setAuthGuard(string $guard): void
    {
        $this->authGuard = $guard;
    }

    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    public function getAuthGuard(): string
    {
        return $this->authGuard;
    }
}
