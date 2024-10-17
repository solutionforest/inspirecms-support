<?php

namespace SolutionForest\InspireCms\Support\Base\Models\Interfaces;

use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

interface HasDtoModel
{
    /**
     * Converts the current model instance to a Data Transfer Object (DTO).
     *
     * @return BaseDto The DTO representation of the model.
     */
    public function toDto(...$args);

    public static function getDtoClass(): string;
}
