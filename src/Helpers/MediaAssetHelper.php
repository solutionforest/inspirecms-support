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
    /**
     * @param \Spatie\MediaLibrary\MediaCollections\FileAdder $fileAdder
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
}
