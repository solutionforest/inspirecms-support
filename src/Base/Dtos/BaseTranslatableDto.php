<?php

namespace SolutionForest\InspireCms\Support\Base\Dtos;

use SolutionForest\InspireCms\Support\Base\Dtos\Concerns\Translatable;

/**
 * Class BaseTranslatableDto
 * 
 * @template TDto of BaseTranslatableDto
 * 
 * @extends BaseDto<TDto>
 */
class BaseTranslatableDto extends BaseDto
{
    use Translatable;

    protected array $translatableAttributes = [];

    /**
     * @return TDto
     */
    public static function fromTranslatableArray(array $parameters, $locale, $fallbackLocale, $availableLocales = [])
    {
        return static::fromArray($parameters)
            ->setLocale($locale)
            ->setFallbackLocale($fallbackLocale)
            ->setAvailableLocales($availableLocales);
    }

    protected function getTranslation(string $name, ?string $locale = null, bool $usingFallback = true)
    {
        if (! in_array($name, $this->translatableAttributes)) {
            return $this->{$name};
        }

        $locale = $locale ?? $this->getLocale();

        return $this->getTranslations($this->{$name}, $locale, $usingFallback);
    }
}
