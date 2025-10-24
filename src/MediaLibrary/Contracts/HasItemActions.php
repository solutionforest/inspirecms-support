<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Contracts;

use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;

interface HasItemActions extends HasActions, HasSchemas
{
    public function getVisibleMediaItemActions(): array;
}
