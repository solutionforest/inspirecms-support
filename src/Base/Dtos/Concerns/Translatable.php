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

    protected array $avilableLocales = [];

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

    public function setAvilableLocales(array $locales)
    {
        $this->avilableLocales = $locales;

        return $this;
    }

    /**
     * @return array
     */
    public function getAvilableLocales()
    {
        return $this->avilableLocales;
    }

    protected function getTranslations($translations, ?string $locale = null, bool $usingFallback = true)
    {
        $locale = $locale ?? $this->getLocale();
        $fallbackLocale = $this->getFallbackLocale();

        return TranslatableHelper::getTranslations($translations, $locale, $fallbackLocale, $usingFallback);
    }
}
