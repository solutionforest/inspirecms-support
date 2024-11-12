<?php

namespace SolutionForest\InspireCms\Support\Base\Dtos\Concerns;

use SolutionForest\InspireCms\Support\Helpers\TranslatableHelper;

trait Translatable
{
    /**
     * @var ?string
     */
    protected $locale;

    /**
     * @var ?string
     */
    protected $fallbackLocale;

    protected array $availableLocales = [];

    /**
     * @param string
     * @return self
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string
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

    public function setAvailableLocales(array $locales)
    {
        $this->availableLocales = $locales;

        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableLocales()
    {
        return $this->availableLocales;
    }

    protected function getTranslations($translations, ?string $locale = null, bool $usingFallback = true)
    {
        $locale = $locale ?? $this->getLocale();
        $fallbackLocale = $this->getFallbackLocale();

        return TranslatableHelper::getTranslations($translations, $locale, $fallbackLocale, $usingFallback);
    }
}
