<?php

namespace SolutionForest\InspireCms\Support\Base\Dtos;

/**
 * Class BaseDto
 *
 * @template TDto of BaseDto
 */
abstract class BaseDto
{
    public function __construct() {}

    /**
     * @return TDto
     */
    public static function fromArray(array $parameters)
    {
        $reflection = new \ReflectionClass(static::class);
        /**
         * @var BaseDto
         */
        $dto = $reflection->newInstanceWithoutConstructor();

        foreach ($parameters as $key => $value) {
            if (! $reflection->hasProperty($key)) {
                continue;
            }

            $property = $reflection->getProperty($key);
            if (! $property->isPublic()) {
                continue;
            }

            $dto->$key = $value;
        }

        return $dto;
    }

    public function __set($name, $value): void
    {
        $this->{$name} = $value;
    }

    public function __toArray(): array
    {
        return get_object_vars($this);
    }

    public function toArray(): array
    {
        return $this->__toArray();
    }

    public function __toString()
    {
        return json_encode($this->__toArray());
    }
}
