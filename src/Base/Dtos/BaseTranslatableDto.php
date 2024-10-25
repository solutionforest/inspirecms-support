<?php

namespace SolutionForest\InspireCms\Support\Base\Dtos;

use SolutionForest\InspireCms\Support\Base\Dtos\Concerns\Translatable;

class BaseTranslatableDto extends BaseDto
{
    use Translatable;

    protected array $translatableAttributes = [];

    /**
     * @return self
     */
    public static function fromArray(array $parameters)
    {
        return parent::fromArray($parameters);
    }

    /**
     * @param  TModle  $model
     * @param string
     * @return self
     */
    public static function fromTranslatableArray(array $parameters, $locale, $fallbackLocale)
    {
        return static::fromArray($parameters)
            ->setLocale($locale)
            ->setFallbackLocale($fallbackLocale);
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
