<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

class MediaLibraryRegistry implements MediaLibraryRegistryInterface
{
    protected string $disk = 'public';

    protected string $directory = 'media';

    protected array $thumbnailCrop = [300, 300];

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
}
