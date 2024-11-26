<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

interface MediaLibraryRegistryInterface
{
    public function setDisk(string $disk): void;

    public function setDirectory(string $directory): void;

    public function setThumbnailCrop(int $width, int $height): void;

    public function getDisk(): string;

    public function getDirectory(): string;

    /**
     * @return array<int>
     */
    public function getThumbnailCrop(): array;
}
