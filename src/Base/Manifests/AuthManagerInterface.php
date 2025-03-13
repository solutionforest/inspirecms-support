<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

interface AuthManagerInterface
{
    /**
     * Set the authentication guard to use.
     */
    public function setAuthGuard(string $guard): void;

    /**
     * Get the current authentication guard.
     */
    public function getAuthGuard(): string;
}
