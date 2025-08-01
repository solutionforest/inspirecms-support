<?php

namespace SolutionForest\InspireCms\Support\Base\Dtos\Concerns;

trait HasFallbackLocale
{
    /**
     * @var ?string
     */
    protected $fallbackLocale = null;

    /**
     * @return self
     */
    public function setFallbackLocale(string $locale)
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }
}
