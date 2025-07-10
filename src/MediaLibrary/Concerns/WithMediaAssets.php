<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Helpers\MediaAssetHelper;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

trait WithMediaAssets
{
    /**
     * @param  string | int  $id
     * @return null | Model & MediaAsset
     */
    protected function resolveAssetRecord($id)
    {
        return $this->getEloquentQuery()->find($id);
    }

    /**
     * @return Collection<Model & MediaAsset>
     */
    protected function resolveAssetRecords(array $ids)
    {
        return $this->getEloquentQuery()->findMany($ids);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getEloquentQuery()
    {
        return static::getMediaAssetModel()::with(['media']);
    }

    protected static function getMediaAssetModel(): string
    {
        return MediaAssetHelper::getMediaAssetModel();
    }

    protected static function getRootLevelParentId(): string | int
    {
        return app(static::getMediaAssetModel())->getRootLevelParentId();
    }
}
