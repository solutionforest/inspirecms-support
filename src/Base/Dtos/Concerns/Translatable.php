<?php

namespace SolutionForest\InspireCms\Support\Base\Dtos\Concerns;

use SolutionForest\InspireCms\Support\Helpers\TranslatableHelper;

trait Translatable
{
    use HasFallbackLocale;

    /**
     * @var ?string
     */
    protected $locale;

    protected array $availableLocales = [];

    /**
     * @param string $locale
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
