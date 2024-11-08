<?php

namespace SolutionForest\InspireCms\Support\Helpers;

class TranslatableHelper
{
    public static function getTranslations($translations, ?string $locale, ?string $fallbackLocale, bool $usingFallback = true)
    {
        if (! $translations || ! is_array($translations)) {
            return $translations;
        }

        if (! $locale) {
            return $translations;
        }

        $value = data_get($translations, $locale);

        if (! $value && $usingFallback && $locale !== $fallbackLocale && $fallbackLocale) {
            $value = data_get($translations, $fallbackLocale);
        }

        return $value;
    }
}
