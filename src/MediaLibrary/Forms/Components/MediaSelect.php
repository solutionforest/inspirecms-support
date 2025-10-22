<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components;

use Filament\Forms\Components\Field;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\Concerns\HasMediaFilterTypes;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\Concerns\LimitsMediaSelection;

class MediaSelect extends Field
{
    use HasMediaFilterTypes;
    use LimitsMediaSelection;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms-support::forms.components.media-select';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->dehydrateStateUsing(function ($state) {
            if (is_array($state)) {
                return array_map(fn ($item) => is_array($item) && isset($item['uid']) ? $item['uid'] : $item, $state);
            }

            return $state;
        });
    }
}
