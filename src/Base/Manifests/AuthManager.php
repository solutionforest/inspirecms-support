<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

class AuthManager implements AuthManagerInterface
{
    protected string $authGuard = 'web';

    public function setAuthGuard(string $guard): void
    {
        $this->authGuard = $guard;
    }

    public function getAuthGuard(): string
    {
        return $this->authGuard;
    }
}
