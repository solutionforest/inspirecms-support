<?php

namespace SolutionForest\InspireCms\Support\Models;

use FFMpeg\FFMpeg;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryManifest;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset as MediaAssetContract;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaAsset extends BaseModel implements MediaAssetContract
{
    use Concerns\BelongsToNestableTree;
    use Concerns\HasAuthor;
    use Concerns\HasRecursiveRelationships;
    use HasUuids;
    use InteractsWithMedia;

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
        if ($this->relationLoaded('media')) {
            return $this->media->first();
        }

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

    public function checkIfMimeType(string $mimeType): bool
    {
        if ($this->isFolder()) {
            return false;
        }
        // Check by mime type
        $mime = $this->getFirstMedia()?->mime_type;
        if (blank($mime)) {
            return false;
        }

        return str_starts_with($mime, $mimeType);
    }

    public function isImage(): bool
    {
        return $this->checkIfMimeType('image/');
    }

    public function isVideo(): bool
    {
        return $this->checkIfMimeType('video/');
    }

    public function isAudio(): bool
    {
        return $this->checkIfMimeType('audio/');
    }

    public function isFolder(): bool
    {
        return $this->is_folder ?? false;
    }

    public function getDisplayedColumns(): array
    {
        $columns = [
            'file_name',
            'mime_type',
            'size',
        ];
        $timestamps = [
            'created_at',
            'updated_at',
            'uploaded_by',
        ];

        $imageColumns = [
            'custom-property.dimensions',
        ];

        $videoColumns = [
            'custom-property.duration',
            'custom-property.resolution',
            'custom-property.channels',
            'custom-property.bit_rate',
            'custom-property.frame_rate',
        ];

        $audioColumns = [
            'custom-property.duration',
            'custom-property.channels',
            'custom-property.bit_rate',
        ];

        if ($this->isFolder()) {
            return [
                'title',
                'created_at',
                'updated_at',
                'created_by',
            ];
        } elseif ($this->isImage()) {
            $columns = array_merge($columns, $imageColumns, $timestamps);
        } elseif ($this->isVideo()) {
            $columns = array_merge($columns, $videoColumns, $timestamps);
        } elseif ($this->isAudio()) {
            $columns = array_merge($columns, $audioColumns, $timestamps);
        }

        return $columns;
    }

    //region Scopes
    public function scopeFolders($query, bool $condition = true)
    {
        return $query->where('is_folder', $condition);
    }
    //endregion Scopes

    //region Dto
    public static function getDtoClass(): string
    {
        return \SolutionForest\InspireCms\Support\Dtos\MediaAssetDto::class;
    }

    public function toDto(...$args)
    {
        $this->loadMissing('media');

        $dtoClass = static::getDtoClass();

        return $dtoClass::fromModel($this);
    }
    //endregion Dto


    public function addMediaWithMappedProperties(string|UploadedFile|TemporaryUploadedFile $file): FileAdder
    {
        $customProperties = [];

        $fileAdder = $this->addMedia($file);
        $mediaItem = $fileAdder->toMediaCollection();
        
        try {

            if ($this->isVideo()) {
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries' => config('media-library.ffmpeg_path'),
                    'ffprobe.binaries' => config('media-library.ffprobe_path'),
                ]);
                $ffprobe = $ffmpeg->getFFProbe()
                    ->streams($mediaItem->getPath()) // extracts streams informations
                    ->videos()                      // filters video streams
                    ->first();

                $customProperties['duration'] = $ffprobe->get('duration');
                $customProperties['width'] = $ffprobe->get('width');
                $customProperties['height'] = $ffprobe->get('height');
                $customProperties['resolution'] = "{$customProperties['width']}x{$customProperties['height']}";
                $customProperties['channels'] = $ffprobe->get('channels');
                $customProperties['bit_rate'] = $ffprobe->get('bit_rate') ?? $ffprobe->get('avg_frame_rate');
                $customProperties['frame_rate'] = $ffprobe->get('r_frame_rate');
                $customProperties['frame_rate_avg'] = $ffprobe->get('avg_frame_rate');
                $customProperties['codec_name'] = $ffprobe->get('codec_name');
                $customProperties['codec_long_name'] = $ffprobe->get('codec_long_name');
            } elseif ($this->isImage()) {
                $dimensions = @getimagesize($mediaItem->getPath());
                if (! empty($dimensions)) {
                    $customProperties['width'] = $dimensions[0] ?? null;
                    $customProperties['height'] = $dimensions[1] ?? null;
                    $customProperties['dimensions'] = "{$customProperties['width']}x{$customProperties['height']}";
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }

        foreach ($customProperties as $key => $value) {
            $mediaItem->setCustomProperty($key, $value);
        }
        $mediaItem->save();

        return $fileAdder;
    }

}
