<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

interface MediaLibraryManifestInterface
{
    public function setDisk(string $disk): void;

    public function setDirectory(string $directory): void;

    public function setModel(string $model): void;

    public function setThumbnailCrop(int $width, int $height): void;

    public function getDisk(): string;

    public function getDirectory(): string;

    public function getModel(): string;

    /**
     * @return array<int>
     */
    public function getThumbnailCrop(): array;
}
