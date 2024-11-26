<?php

namespace SolutionForest\InspireCms\Support\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Support\Base\Manifests\MediaLibraryRegistryInterface;

/**
 * @method static void setDisk(string $disk)
 * @method static void setDirectory(string $directory)
 * @method static void setThumbnailCrop(int $width, int $height)
 * @method static string getDisk()
 * @method static string getDirectory()
 * @method static array getThumbnailCrop
 *
 * @see \SolutionForest\InspireCms\Support\Base\Manifests\MediaLibraryRegistry
 */
class MediaLibraryRegistry extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return MediaLibraryRegistryInterface::class;
    }
}
