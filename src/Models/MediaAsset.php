<?php

namespace SolutionForest\InspireCms\Support\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\InspireCms\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryManifest;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset as MediaAssetContract;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaAsset extends BaseModel implements MediaAssetContract
{
    use Concerns\BelongToCmsNestableTree;
    use Concerns\HasAuthor;
    use Concerns\NestableTrait;
    use HasUuids;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_folder' => 'boolean',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        [$width, $height] = MediaLibraryManifest::getThumbnailCrop();
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Crop, $width, $height)
            ->nonQueued();
    }

    public function getFirstMedia(): ?Media
    {
        return $this->media()->first();
    }

    public function getUrl(string $conversionName = ''): ?string
    {
        $media = $this->getFirstMedia();

        return $media?->getUrl($conversionName);
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->getUrl('preview');
    }

    public function getThumbnail(): string
    {
        if ($this->isImage()) {
            return $this->getThumbnailUrl();
        }
        if ($this->isFolder()) {
            return 'heroicon-o-folder';
        }

        $media = $this->getFirstMedia();
        $extension = filled($media?->file_name) ? (string) str($media->file_name)->afterLast('.') : null;

        // Check by mime type
        $mime = $media?->mime_type;

        if (blank($mime)) {
            return 'heroicon-s-x-mark';
        }

        if (str_starts_with($mime, 'audio/')) {
            return 'heroicon-o-musical-note';
        }

        if (str_starts_with($mime, 'video/')) {
            return 'heroicon-o-film';
        }

        if (str_starts_with($mime, 'application/pdf')) {
            return 'inspirecms-support::pdf';
        }

        if ($extension === 'xlsx') {
            return 'inspirecms-support::excel';
        }

        return 'heroicon-o-document';
    }

    public function isImage(): bool
    {
        if ($this->isFolder()) {
            return false;
        }
        // Check by mime type
        $mime = $this->getFirstMedia()?->mime_type;
        if (blank($mime)) {
            return false;
        }

        return str_starts_with($mime, 'image/');
    }

    public function isFolder(): bool
    {
        return $this->is_folder;
    }

    //region Scopes
    public function scopeFolders($query, bool $condition = true)
    {
        return $query->where('is_folder', $condition);
    }
    //endregion Scopes

    public static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (blank($model->{$model->getNestableParentIdColumn()})) {
                $model->{$model->getNestableParentIdColumn()} = $model->fallbackParentId();
            }
        });
        static::deleting(function (self $model) {
            $model->children()->delete();
        });
        static::forceDeleting(function (self $model) {
            $model->children()->forceDelete();
        });
    }

    //region Nestable
    protected function getParentId()
    {
        return $this->{$this->getNestableParentIdColumn()} ?? $this->fallbackParentId();
    }

    public function getNestableParentIdColumn(): string
    {
        return 'parent_id';
    }

    protected function fallbackParentId()
    {
        return $this->getNestableRootValue();
    }

    public function getNestableRootValue(): int | string
    {
        return KeyHelper::generateMinUuid();
    }
    //endregion Nestable
}
