<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Concerns;

use Illuminate\Support\Collection;

interface HasItemBulkActions
{
    public function getSelectedMediaAssets(): Collection;

    public function getSelectedMediaAssetIds(): array;
}
