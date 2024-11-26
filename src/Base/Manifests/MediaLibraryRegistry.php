<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

use SolutionForest\InspireCms\Support\Models\MediaAsset;

class MediaLibraryRegistry implements MediaLibraryRegistryInterface
{
    protected string $disk = 'public';

    protected string $directory = 'media';

    protected string $model;

    protected array $thumbnailCrop = [300, 300];

    public function __construct()
    {
        $this->model = MediaAsset::class;
    }

    public function setDisk(string $disk): void
    {
        $this->disk = $disk;
    }

    public function setDirectory(string $directory): void
    {
        $this->directory = $directory;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
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

    public function getModel(): string
    {
        return $this->model;
    }

    public function getThumbnailCrop(): array
    {
        return $this->thumbnailCrop;
    }
}
