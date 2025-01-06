<?php

namespace SolutionForest\InspireCms\Support\Models;

use FFMpeg\FFMpeg;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset as MediaAssetContract;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @property string $id
 * @property string $title
 * @property string|int $nestable_id
 * @property string $parent_id
 * @property bool $is_folder
 * @property ?string $caption
 * @property ?string $description
 * @property ?string $author_type
 * @property ?string $author_id
 * @property-read ?\Illuminate\Support\Carbon $created_at
 * @property-read ?\Illuminate\Support\Carbon $updated_at
 */
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

    /**
     * @return int|string|null
     */
    public function getRootLevelParentId()
    {
        return KeyHelper::generateMinUuid();
    }

    /** {@inheritDoc} */
    public function registerMediaConversions($media = null): void
    {
        [$width, $height] = MediaLibraryRegistry::getThumbnailCrop();
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Crop, $width, $height)
            ->nonQueued();
    }

    /** {@inheritDoc} */
    public function getFirstMedia()
    {
        if ($this->relationLoaded('media')) {
            return $this->media->first();
        }

        return $this->media()->first();
    }

    public function getUrl(string $conversionName = '')
    {
        $media = $this->getFirstMedia();

        return $media?->getUrl($conversionName);
    }

    public function getThumbnailUrl()
    {
        return $this->getUrl('preview');
    }

    public function getThumbnail()
    {
        if ($this->isImage()) {
            return $this->getThumbnailUrl();
        }
        if ($this->isFolder()) {
            return 'heroicon-s-folder';
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

    public function isImage()
    {
        return $this->checkIfMimeType('image/');
    }

    public function isVideo()
    {
        return $this->checkIfMimeType('video/');
    }

    public function isAudio()
    {
        return $this->checkIfMimeType('audio/');
    }

    public function isFolder()
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

    // region Scopes
    public function scopeFolders($query, bool $condition = true)
    {
        return $query->where('is_folder', $condition);
    }
    // endregion Scopes

    // region Dto
    public static function getDtoClass()
    {
        return \SolutionForest\InspireCms\Support\Dtos\MediaAssetDto::class;
    }

    public function toDto(...$args)
    {
        $this->loadMissing('media');

        $dtoClass = static::getDtoClass();

        return $dtoClass::fromModel($this);
    }
    // endregion Dto

    protected function shouldMapVideoPropertiesWithFfmpeg(): bool
    {
        return $this->isVideo() && MediaLibraryRegistry::shouldMapVideoPropertiesWithFfmpeg();
    }

    protected static function getPropertiesForVideo(string $videoPath, ?array $customProperties = null): array
    {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => config('media-library.ffmpeg_path'),
            'ffprobe.binaries' => config('media-library.ffprobe_path'),
        ]);
        $ffprobe = $ffmpeg->getFFProbe()
            ->streams($videoPath) // extracts streams informations
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

        return $customProperties;
    }

    public function addMediaWithMappedProperties(string | UploadedFile | TemporaryUploadedFile $file): FileAdder
    {
        $customProperties = [];
        $shouldRetry = false;

        $fileAdder = $this->addMedia($file);
        $mediaItem = $fileAdder->toMediaCollection();
        $contents = Storage::disk(MediaLibraryRegistry::getDisk())->get($mediaItem->getPathRelativeToRoot());
        $fileExtension = pathinfo($mediaItem->file_name, PATHINFO_EXTENSION);

        try {
            if ($this->shouldMapVideoPropertiesWithFfmpeg()) {
                $videoPath = $mediaItem->getPath();
                $customProperties = static::getPropertiesForVideo($videoPath, $customProperties);
            }
        } catch (\Exception $e) {
            $shouldRetry = true;
        }

        try {
            if ($shouldRetry && $this->shouldMapVideoPropertiesWithFfmpeg()) {
                $tempFilePath = 'temp/' . time() . '.' . $fileExtension;
                Storage::disk('local')->put($tempFilePath, $contents);
                $tempFullPath = Storage::disk('local')->path($tempFilePath);
                $customProperties = static::getPropertiesForVideo($tempFullPath, $customProperties);
                Storage::disk('local')->delete($tempFilePath);
            }

            if ($this->isImage() && ! empty($contents)) {
                $im = imagecreatefromstring($contents);
                $customProperties['width'] = imagesx($im) ?? null;
                $customProperties['height'] = imagesy($im) ?? null;
                $customProperties['dimensions'] = "{$customProperties['width']}x{$customProperties['height']}";
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
