<?php

namespace SolutionForest\InspireCms\Support\Base\Dtos;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Base\Dtos\Concerns\Translatable;

/**
 * Class BaseTranslatableModelDto
 *
 * @template TModle of Model
 * @template TDto of BaseTranslatableModelDto
 *
 * @extends BaseModelDto<TModle,TDto>
 */
abstract class BaseTranslatableModelDto extends BaseModelDto
{
    use Translatable;

    protected array $translatableAttributes = [];

    /**
     * @param  TModle  $model
     * @param string $locale
     * @param array $availableLocales
     * @return TDto
     */
    public static function fromTranslatableModel($model, $locale, $availableLocales = [])
    {
        $dto = static::fromModel($model)->setLocale($locale)->setAvailableLocales($availableLocales);

        if (in_array('Spatie\Translatable\HasTranslations', class_uses_recursive($model))) {
            $dto = $dto->setFallbackLocale($model->getFallbackLocale());
        }

        return $dto;
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
