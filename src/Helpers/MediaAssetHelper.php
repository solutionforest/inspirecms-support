<?php

namespace SolutionForest\InspireCms\Support\Helpers;

use Exception;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\FileAdder;

class MediaAssetHelper
{
    private const DISPLAYED_COLUMNS_DEFAULT = [
        'created_at',
        'updated_at',
        'uploaded_by',
    ];

    private const DISPLAYED_COLUMNS_NON_FOLDER = [
        'model_id',
        'file_name',
        'mime_type',
        'size',
        ...self::DISPLAYED_COLUMNS_DEFAULT,
    ];

    private const DISPLAYED_COLUMNS_FOLDER = [
        'title',
        ...self::DISPLAYED_COLUMNS_DEFAULT,
    ];

    /**
     * @throws \Throwable
     */
    public static function validateMediaBeforeAddFromUrl(FileAdder $fileAdder)
    {
        // Validate size
        if (($maxSize = MediaLibraryRegistry::getMaxSize()) || ($minSize = MediaLibraryRegistry::getMinSize())) {

            // Get size from temporary file
            /**
             * @var string
             */
            $tempFilePath = $fileAdder->getFile();
            $tempFileSize = filesize($tempFilePath);

            if (isset($maxSize) && $maxSize != null && $maxSize > -1 && $tempFileSize > $maxSize) {
                $message = "File size of {$tempFileSize} bytes exceeds the maximum allowed size of {$maxSize} bytes.";

                throw new FileIsTooBig($message);
            }

            if (isset($minSize) && $minSize != null && $minSize > -1 && $tempFileSize < $minSize) {
                throw new Exception("The file size is less than the minimum allowed size of {$minSize} bytes.");
            }
        }
    }

    /**
     * @return class-string<Model | MediaAsset>
     */
    public static function getMediaAssetModel()
    {
        return ModelRegistry::get(MediaAsset::class);
    }

    public static function getDefaultCollectionName(): string
    {
        return 'default';
    }

    public static function getDisk(): string
    {
        return MediaLibraryRegistry::getDisk();
    }

    public static function configureFileUploadField(FileUpload $field)
    {
        if (MediaLibraryRegistry::hasLimitedMimeTypes()) {
            $field->acceptedFileTypes(MediaLibraryRegistry::getLimitedMimeTypes());
        }

        if (($maxSize = MediaLibraryRegistry::getMaxSize()) !== null) {
            $field->maxSize($maxSize);
        }
        if (($minSize = MediaLibraryRegistry::getMinSize()) !== null) {
            $field->minSize($minSize);
        }

        return $field;
    }

    public static function getMediaAssetDisplayedColumnsForFolder(): array
    {
        return array_unique(self::DISPLAYED_COLUMNS_FOLDER);
    }

    public static function getMediaAssetDisplayedColumnsForNonFolder(): array
    {
        return array_unique(self::DISPLAYED_COLUMNS_NON_FOLDER);
    }

    public static function getMediaAssetDisplayedColumnsForImage(): array
    {
        return array_unique(array_merge(
            static::getMediaAssetDisplayedColumnsForNonFolder(),
            [
                'custom-property.dimensions',
            ]
        ));
    }

    public static function getMediaAssetDisplayedColumnsForVideo(): array
    {
        return array_unique(array_merge(
            static::getMediaAssetDisplayedColumnsForNonFolder(),
            [
                'custom-property.duration',
                'custom-property.resolution',
                'custom-property.channels',
                'custom-property.bit_rate',
                'custom-property.frame_rate',
            ]
        ));
    }

    public static function getMediaAssetDisplayedColumnsForAudio(): array
    {
        return array_unique(array_merge(
            static::getMediaAssetDisplayedColumnsForNonFolder(),
            [
                'custom-property.duration',
                'custom-property.channels',
                'custom-property.bit_rate',
            ]
        ));
    }
}
