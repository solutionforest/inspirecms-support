<?php

namespace SolutionForest\InspireCms\Support\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
                'title' => str(basename($url))->before('?')->toString(),
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

    /**
     * @throws \Throwable
     */
    protected static function createMediaFromUrl(MediaAsset | Model $mediaAsset, string $url)
    {
        $limitedMimeTypes = MediaLibraryRegistry::hasLimitedMimeTypes() ? MediaLibraryRegistry::getLimitedMimeTypes() : [];
        $fileAdder = $mediaAsset->addMediaFromUrl($url, $limitedMimeTypes);

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
                if ($file instanceof UploadedFile || is_string($file)) {
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
    public static function createMediaAssetFromFile(string | UploadedFile $file, ?string $parentKey = null)
    {
        try {

            DB::beginTransaction();

            /**
             * @var MediaAsset | Model
             */
            $mediaAsset = MediaAssetHelper::getMediaAssetModel()::create([
                'parent_id' => static::ensureParentKeyBeforeCreate($parentKey),
                'title' => $file->getClientOriginalName(),
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

    protected static function createMediaFromFile(MediaAsset | Model $mediaAsset, string | UploadedFile $file)
    {
        $fileAdder = $mediaAsset->addMedia($file);

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

                $disk = Storage::disk($media->disk);
                $originalPath = $media->getPathRelativeToRoot();

                // Replace the existing media file with the new file
                $disk->delete($originalPath);
                $disk->putFileAs(dirname($originalPath), $tempFileRealPath, $media->file_name);

                static::regenerateMediaConvertsions($media);

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

                $disk = Storage::disk($media->disk);
                $originalPath = $media->getPathRelativeToRoot();

                // Replace the existing media file with the new file
                $disk->delete($originalPath);
                $disk->putFileAs(dirname($originalPath), $file, $media->file_name);

                static::regenerateMediaConvertsions($media);

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
}
