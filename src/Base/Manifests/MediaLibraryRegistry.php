<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

use Closure;

class MediaLibraryRegistry implements MediaLibraryRegistryInterface
{
    protected string $disk = 'public';

    protected string $directory = 'media';

    protected array $thumbnailCrop = [300, 300];

    protected bool $shouldMapVideoPropertiesWithFfmpeg = false;

    /**
     * @var array<Closure>
     */
    protected array $registerConversionsUsing = [];

    protected ?array $limitedMimeTypes = null;

    protected ?int $maxSize = null;

    protected ?int $minSize = null;

    public function setDisk(string $disk): void
    {
        $this->disk = $disk;
    }

    public function setDirectory(string $directory): void
    {
        $this->directory = $directory;
    }

    public function setThumbnailCrop(int $width, int $height): void
    {
        $this->thumbnailCrop = [$width, $height];
    }

    public function setShouldMapVideoPropertiesWithFfmpeg(bool $condition): void
    {
        $this->shouldMapVideoPropertiesWithFfmpeg = $condition;
    }

    public function setLimitedMimeTypes(array $limitedMimeTypes): void
    {
        $this->limitedMimeTypes = $limitedMimeTypes;
    }

    public function registerConversionUsing(Closure $callback, bool $merge = true): void
    {
        if ($merge) {
            $this->registerConversionsUsing[] = $callback;
        } else {
            $this->registerConversionsUsing = [$callback];
        }
    }

    public function setMaxSize(?int $maxSize): void
    {
        $this->maxSize = $maxSize;
    }

    public function setMinSize(?int $minSize): void
    {
        $this->minSize = $minSize;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getThumbnailCrop(): array
    {
        return $this->thumbnailCrop;
    }

    public function shouldMapVideoPropertiesWithFfmpeg(): bool
    {
        return $this->shouldMapVideoPropertiesWithFfmpeg;
    }

    public function hasLimitedMimeTypes(): bool
    {
        return !empty($this->getLimitedMimeTypes());
    }

    public function getLimitedMimeTypes(): array
    {
        return $this->limitedMimeTypes ?? [];
    }

    public function getRegisterConversionsUsing(): array
    {
        return $this->registerConversionsUsing;
    }

    public function getMaxSize(): ?int
    {
        return $this->maxSize;
    }

    public function getMinSize(): ?int
    {
        return $this->minSize;
    }
}
