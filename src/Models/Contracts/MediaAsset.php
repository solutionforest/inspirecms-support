<?php

namespace SolutionForest\InspireCms\Support\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\BelongsToNestableTree;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MediaAsset extends BelongsToNestableTree, HasDtoModel, HasMedia
{
    /**
     * @param  null | Model | Media  $media
     */
    public function registerMediaConversions($media = null): void;

    /**
     * @return null | Model | Media
     */
    public function getFirstMedia();

    public function getUrl(string $conversionName = ''): ?string;

    public function getThumbnailUrl(): ?string;

    public function getThumbnail(): string;

    public function isImage(): bool;

    public function isFolder(): bool;
}
