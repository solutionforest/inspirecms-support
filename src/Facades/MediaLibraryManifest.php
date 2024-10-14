<?php

namespace SolutionForest\InspireCms\Support\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Support\Base\Manifests\MediaLibraryManifestInterface;

/**
 * @method static void setDisk(string $disk)
 * @method static void setDirectory(string $directory)
 * @method static void setModel(string $model)
 * @method static void setThumbnailCrop(int $width, int $height)
 * @method static string getDisk()
 * @method static string getDirectory()
 * @method static string getModel()
 * @method static array getThumbnailCrop
 *
 * @see \SolutionForest\InspireCms\Support\Base\Manifests\MediaLibraryManifest
 */
class MediaLibraryManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return MediaLibraryManifestInterface::class;
    }
}
