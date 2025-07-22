<?php

namespace SolutionForest\InspireCms\Support\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\Helpers\MediaAssetHelper;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaAssetService
{
    /**
     * @return MediaAsset | Model
     *
     * @throws \Throwable
     */
    public static function createMediaAssetFromUrl($url, ?string $parentKey = null)
    {
        try {
            if (! is_string($url) || empty($url)) {
                throw new \InvalidArgumentException('The URL must be a non-empty string.');
            }

            DB::beginTransaction();

            /**
             * @var MediaAsset | Model
             */
            $mediaAsset = MediaAssetHelper::getMediaAssetModel()::create([
                'parent_id' => static::ensureParentKeyBeforeCreate($parentKey),
                'title' => static::getMediaNameFromUrl($url),
            ]);

            [$mediaAsset, $media, $fileAdder] = static::createMediaFromUrl($mediaAsset, $url);

            $mediaAsset->syncMediaProperties($media);

            DB::commit();

            return $mediaAsset;

        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    protected static function getMediaNameFromUrl(string $url): string
    {
        $filename = basename(parse_url($url, PHP_URL_PATH));
        $filename = urldecode($filename);

        if ($filename === '') {
            $filename = 'file';
        }

        return pathinfo($filename, PATHINFO_FILENAME);
    }

    /**
     * @throws \Throwable
     */
    protected static function createMediaFromUrl(MediaAsset | Model $mediaAsset, string $url, ?string $name = null)
    {
        $limitedMimeTypes = MediaLibraryRegistry::hasLimitedMimeTypes() ? MediaLibraryRegistry::getLimitedMimeTypes() : [];
        $fileAdder = $mediaAsset
            ->addMediaFromUrl($url, $limitedMimeTypes)
            ->usingName($name ?? static::getMediaNameFromUrl($url));

        MediaAssetHelper::validateMediaBeforeAddFromUrl($fileAdder);

        $mediaAsset->title = $fileAdder->getFileName();

        $media = $fileAdder->toMediaCollection(
            collectionName: MediaAssetHelper::getDefaultCollectionName(),
            diskName: MediaAssetHelper::getDisk()
        );

        return [$mediaAsset, $media, $fileAdder];
    }

    public static function createMediaAssetFromFiles(array $files, ?string $parentKey = null)
    {
        $fails = [];
        foreach ($files as $file) {
            try {
                if ($file instanceof TemporaryUploadedFile || $file instanceof UploadedFile || is_string($file)) {
                    static::createMediaAssetFromFile(
                        file: $file,
                        parentKey: $parentKey
                    );
                } else {
                    $fails[] = [
                        'file' => $file,
                        'error' => 'Invalid file type. Expected UploadedFile or string (file path).',
                    ];
                }
            } catch (\Throwable $th) {
                $fails[] = [
                    'file' => $file,
                    'error' => $th->getMessage(),
                ];
            }
        }

        return [
            'success' => count($files) - count($fails),
            'fails' => $fails,
        ];
    }

    /**
     * @return MediaAsset | Model
     */
    public static function createMediaAssetFromFile(string | UploadedFile | TemporaryUploadedFile $file, ?string $parentKey = null)
    {
        try {

            DB::beginTransaction();

            /**
             * @var MediaAsset | Model
             */
            $mediaAsset = MediaAssetHelper::getMediaAssetModel()::create([
                'parent_id' => static::ensureParentKeyBeforeCreate($parentKey),
                'title' => static::getMediaNameFromFile($file),
            ]);

            [$mediaAsset, $media, $fileAdder] = static::createMediaFromFile($mediaAsset, $file);

            $mediaAsset->syncMediaProperties($media);

            DB::commit();

            return $mediaAsset;

        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    protected static function getMediaNameFromFile(UploadedFile | TemporaryUploadedFile $file): string
    {
        $filename = $file->getClientOriginalName();

        return pathinfo($filename, PATHINFO_FILENAME);
    }

    protected static function createMediaFromFile(MediaAsset | Model $mediaAsset, string | UploadedFile $file, ?string $name = null)
    {
        $fileAdder = $mediaAsset
            ->addMedia($file)
            ->usingName($name ?? static::getMediaNameFromFile($file));

        $media = $fileAdder
            ->toMediaCollection(
                collectionName: MediaAssetHelper::getDefaultCollectionName(),
                diskName: MediaAssetHelper::getDisk()
            );

        return [$mediaAsset, $media, $fileAdder];
    }

    public static function uploadMediaFromUrlWithoutDelete(MediaAsset | Model $mediaAsset, string $url)
    {
        try {

            DB::beginTransaction();

            // Ensure the media asset exists
            if (! $mediaAsset->exists) {
                throw new Exception('Media asset does not exist.');
            }

            if (($media = $mediaAsset->getFirstMedia())) {

                $tempMediaAssetModel = app(MediaAssetHelper::getMediaAssetModel());
                $limitedMimeTypes = MediaLibraryRegistry::hasLimitedMimeTypes() ? MediaLibraryRegistry::getLimitedMimeTypes() : [];
                $fileAdder = $tempMediaAssetModel->addMediaFromUrl($url, $limitedMimeTypes);

                MediaAssetHelper::validateMediaBeforeAddFromUrl($fileAdder);

                $tempFileRealPath = $fileAdder->getFile();

                static::validateMediaUpdateWithoutDelete($mediaAsset, $tempFileRealPath);

                static::replaceOriginalMediaFile($media, $tempFileRealPath);

            } else {
                // If no existing media, just add the new file
                [$mediaAsset, $media, $fileAdder] = static::createMediaFromUrl($mediaAsset, $url);
            }

            $mediaAsset->syncMediaProperties($mediaAsset->getFirstMedia());

            DB::commit();

            return $mediaAsset;

        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * Avoid delete 'media' model, just delete the media file and replace it with the new file.
     * Keep:
     * - file_path
     * - file_name
     *
     * @return MediaAsset|Model
     *
     * @throws \Throwable
     */
    public static function updateMediaFromFileWithoutDelete(MediaAsset | Model $mediaAsset, string | UploadedFile $file)
    {
        try {

            DB::beginTransaction();

            // Ensure the media asset exists
            if (! $mediaAsset->exists) {
                throw new Exception('Media asset does not exist.');
            }

            if (($media = $mediaAsset->getFirstMedia())) {

                static::validateMediaUpdateWithoutDelete($mediaAsset, $file);

                static::replaceOriginalMediaFile($media, $file);

            } else {
                // If no existing media, just add the new file
                [$mediaAsset, $media, $fileAdder] = static::createMediaFromFile($mediaAsset, $file);
            }

            $mediaAsset->syncMediaProperties($mediaAsset->getFirstMedia());

            DB::commit();

            return $mediaAsset;

        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public static function regenerateMediaConvertsions(Media $media)
    {
        // Mark the media as conversion not generated, so it will be regenerated
        $media->markAsConversionNotGenerated($media->collection_name);
        $fileManipulator = app(FileManipulator::class);
        $fileManipulator->createDerivedFiles($media);
    }

    protected static function ensureParentKeyBeforeCreate($parentKey)
    {
        if (empty($parentKey) || is_null($parentKey)) {
            return KeyHelper::generateMinUuid();
        }

        return $parentKey;
    }

    /**
     * @throws \Throwable
     */
    protected static function validateMediaUpdateWithoutDelete(MediaAsset $originalMediaAsset, $newFile)
    {
        if ($originalMediaAsset->isFolder()) {
            throw new Exception('Cannot update a folder with a file.');
        }

        $originalFile = $originalMediaAsset->getFirstMedia();

        if (! $originalFile) {
            throw new Exception('Media asset does not have an associated file.');
        }

        // Validate file type can be replace (e.g., image to image, video to video)
        if ($newFile instanceof UploadedFile) {
            $newFileType = $newFile->getMimeType();
        } elseif (is_string($newFile)) {
            $newFileType = mime_content_type($newFile);
        } else {
            throw new Exception('Invalid file type. Expected UploadedFile or string (file path).');
        }

        // Check the new file can be convert to an image
        if ($originalMediaAsset->isImage()) {
            if (! str_starts_with($newFileType, 'image/')) {
                throw new Exception('The new file must be an image type.');
            }
        }
        // Check if the new file type matches the original file type
        elseif ($newFileType !== $originalFile->mime_type) {
            throw new Exception('The new file type does not match the original file type.');
        }
    }

    protected static function replaceOriginalMediaFile(Media $originalMedia, $newFile)
    {
        $originalExtension = pathinfo($originalMedia->getPathRelativeToRoot(), PATHINFO_EXTENSION);

        if ($newFile instanceof UploadedFile) {
            $newFileExtension = $newFile->getClientOriginalExtension();
        } elseif (is_string($newFile)) {
            $newFileExtension = pathinfo($newFile, PATHINFO_EXTENSION);
        } else {
            throw new Exception('Invalid file type. Expected UploadedFile or string (file path).');
        }

        // Replace with the new file
        if (str_starts_with($originalMedia->mime_type, 'image/')) {
            // If extensions are same, use the original file name
            if ($newFileExtension === $originalExtension) {
                Storage::disk($originalMedia->disk)->putFileAs(
                    dirname($originalMedia->getPathRelativeToRoot()),
                    $newFile,
                    $originalMedia->file_name
                );
            }
            // Otherwise, try converting the new file to the original extension
            else {
                $tempFilePath = static::convertImageAs($newFile, $originalExtension);
                Storage::disk($originalMedia->disk)->putFileAs(
                    dirname($originalMedia->getPathRelativeToRoot()),
                    $tempFilePath,
                    $originalMedia->file_name
                );
                // Delete the temporary file
                unlink($tempFilePath);
            }

        } else {
            Storage::disk($originalMedia->disk)->putFileAs(
                dirname($originalMedia->getPathRelativeToRoot()),
                $newFile,
                $originalMedia->file_name
            );
        }

        // Regenerate conversions
        static::regenerateMediaConvertsions($originalMedia);
    }

    protected static function convertImageAs($file, string $targetExtension)
    {
        if ($file instanceof UploadedFile) {
            $fileContent = file_get_contents($file->getRealPath());
        } elseif (is_string($file)) {
            $fileContent = file_get_contents($file);
        } else {
            throw new Exception('Invalid file type. Expected UploadedFile or string (file path).');
        }
        $tempFilePath = tempnam(sys_get_temp_dir(), 'media_asset_');
        $image = imagecreatefromstring($fileContent);

        if ($image === false) {
            throw new Exception('Failed to create image from file.');
        }

        switch ($targetExtension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($image, $tempFilePath);

                break;
            case 'png':
                imagepng($image, $tempFilePath);

                break;
            case 'gif':
                imagegif($image, $tempFilePath);

                break;
            default:
                throw new Exception("Unsupported target extension: {$targetExtension}");
        }

        imagedestroy($image);

        return $tempFilePath;
    }
}
