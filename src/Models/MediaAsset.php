<?php

namespace SolutionForest\InspireCms\Support\Models;

use FFMpeg\FFMpeg;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset as MediaAssetContract;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;

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
        foreach (MediaLibraryRegistry::getRegisterConversionsUsing() as $callback) {
            $callback($this, $media);
        }

        [$thumbW, $thumbH] = MediaLibraryRegistry::getThumbnailCrop();
        $thumbConversion = 'preview';

        $callbackThumbConversion = $this
            ->addMediaConversion($thumbConversion)
            ->fit(Fit::Crop, $thumbW, $thumbH)
            ->nonQueued();

        if ($media && $media->extension == 'webp') {
            $callbackThumbConversion->format('FORMAT_WEBP');
        }
    }

    public function getUrl(string $conversionName = '', bool $isAbsolute = true): ?string
    {
        if ($this->isFolder()) {
            return null;
        }

        $result = $this->getFirstMediaUrl(collectionName: MediaAssetHelper::getDefaultCollectionName(), conversionName: $conversionName);

        // For spatie/laravel-medialibrary v11
        // Fallback to getLastMediaUrl if getFirstMediaUrl is not available
        if (blank($result) && method_exists($this, 'getLastMediaUrl')) {
            $result = $this->getLastMediaUrl(collectionName: MediaAssetHelper::getDefaultCollectionName(), conversionName: $conversionName);
        }

        if (! $isAbsolute && filled($result)) {
            $result = str_replace(config('app.url'), '', $result);
        }

        return $result ?: null;
    }

    public function getThumbnailUrl(bool $isAbsolute = true)
    {
        return $this->getUrl(conversionName: 'preview', isAbsolute: $isAbsolute);
    }

    public function getActiveThumbnail()
    {
        if ($this->isFolder()) {
            return 'heroicon-s-folder-open';
        }

        return $this->getThumbnail();
    }

    public function getThumbnail()
    {
        if ($this->isFolder()) {
            return 'heroicon-s-folder';
        }

        $media = $this->getFirstMedia();
        $extension = filled($media?->file_name) ? (string) str($media->file_name)->afterLast('.') : null;
        if (blank($media?->mime_type)) {
            return 'heroicon-s-x-mark';
        }

        if ($media?->hasGeneratedConversion('preview')) {
            return $this->getThumbnailUrl();
        }

        if ($this->isImage()) {
            // Fallback to default image if no thumbnail is available
            return $this->getUrl();
        }

        if ($this->isSvg()) {
            return $this->getUrl(); // return 'inspirecms::svg';
        }

        if ($this->isAudio()) {
            return 'heroicon-o-musical-note';
        }

        if ($this->isVideo()) {
            return 'heroicon-o-film';
        }

        if ($this->isPdf()) {
            return 'inspirecms::pdf';
        }

        if ($extension === 'xlsx') {
            return 'inspirecms::excel';
        }

        return 'heroicon-o-document';
    }

    public function isSvg()
    {
        return $this->matchesMimeType('image/svg+xml') || $this->matchesMimeType('image/svg');
    }

    public function isImage()
    {
        return $this->matchesMimeType('image/') && ! $this->isSvg();
    }

    public function isVideo()
    {
        return $this->matchesMimeType('video/');
    }

    public function isAudio()
    {
        return $this->matchesMimeType('audio/');
    }

    public function isPdf()
    {
        $mimeTypes = ['application/pdf', 'application/x-pdf', 'application/acrobat', 'applications/vnd.pdf', 'text/pdf', 'text/x-pdf'];

        foreach ($mimeTypes as $mimeType) {
            if ($this->matchesMimeType($mimeType)) {
                return true;
            }
        }

        return false;
    }

    public function isFolder()
    {
        return $this->is_folder ?? false;
    }

    public function getDisplayedColumns(): array
    {
        $columns = [
            'model_id',
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

    /** {@inheritDoc} */
    public function syncMediaProperties($media)
    {
        // Adjust properties
        $customProperties = [];
        $shouldRetry = false;

        $contents = Storage::disk(MediaLibraryRegistry::getDisk())->get($media->getPathRelativeToRoot());
        $fileExtension = pathinfo($media->file_name, PATHINFO_EXTENSION);

        try {
            if ($this->shouldMapVideoPropertiesWithFfmpeg()) {
                $videoPath = $media->getPath();
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
            $media->setCustomProperty($key, $value);
        }
        $media->save();

        return $media;
    }

    protected function matchesMimeType(string $mimeType): bool
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
}
