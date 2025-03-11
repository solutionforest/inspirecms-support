<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

interface AuthManagerInterface
{
    /**
     * Set the authentication guard to use.
     *
     * @param string $guard
     * @return void
     */
    public function setAuthGuard(string $guard): void;

    /**
     * Get the current authentication guard.
     *
     * @return string
     */
    public function getAuthGuard(): string;
}