<?php

namespace SolutionForest\InspireCms\Support\Base\Models\Interfaces;

use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

/**
 * @template TDto of BaseDto
 */
interface HasDtoModel
{
    /**
     * Converts the current model instance to a Data Transfer Object (DTO).
     *
     * @return TDto The DTO representation of the model.
     */
    public function toDto(...$args);

    /**
     * Get the fully qualified class name of the Data Transfer Object (DTO) associated with the model.
     *
     * @return string The fully qualified class name of the DTO.
     */
    public static function getDtoClass();
}
