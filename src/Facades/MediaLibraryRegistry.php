<?php

namespace SolutionForest\InspireCms\Support\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Support\Base\Manifests\MediaLibraryRegistryInterface;

/**
 * @method static void setDisk(string $disk)
 * @method static void setThumbnailCrop(int $width, int $height)
 * @method static void setShouldMapVideoPropertiesWithFfmpeg(bool $condition)
 * @method static void setLimitedMimeTypes(array $limitedMimeTypes)
 * @method static void registerConversionUsing(\Closure $callback, bool $merge = true)
 * @method static void setMaxSize(?int $maxSize)
 * @method static void setMinSize(?int $minSize)
 * @method static string getDisk()
 * @method static array getThumbnailCrop()
 * @method static bool shouldMapVideoPropertiesWithFfmpeg()
 * @method static bool hasLimitedMimeTypes()
 * @method static array getLimitedMimeTypes()
 * @method static array getRegisterConversionsUsing()
 * @method static ?int getMaxSize()
 * @method static ?int getMinSize()
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
