<?php

namespace SolutionForest\InspireCms\Support\Macros;

/**
 * @see \Illuminate\Database\Schema\Blueprint
 */
class BlueprintMarcos
{
    public function author()
    {
        return function (string $userType = 'integer', bool $nullable = false) {
            if ($userType === 'integer') {
                if ($nullable) {
                    $this->nullableMorphs('author');
                } else {
                    $this->morphs('author');
                }
            } else {
                if ($nullable) {
                    $this->nullableUuidMorphs('author');
                } else {
                    $this->uuidMorphs('author');
                }
            }
        };
    }
}
