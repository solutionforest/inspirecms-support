<?php

namespace SolutionForest\InspireCms\Support\Base\Manifests;

interface MediaLibraryRegistryInterface
{
    public function setDisk(string $disk): void;

    public function setDirectory(string $directory): void;

    public function setThumbnailCrop(int $width, int $height): void;

    public function setShouldMapVideoPropertiesWithFfmpeg(bool $condition): void;

    public function setLimitedMimeTypes(array $limitedMimeTypes): void;

    public function registerConversionUsing(\Closure $callback, bool $merge = true): void;

    public function setMaxSize(?int $maxSize): void;

    public function setMinSize(?int $minSize): void;

    public function getDisk(): string;

    public function getDirectory(): string;

    /**
     * @return array<int>
     */
    public function getThumbnailCrop(): array;

    public function shouldMapVideoPropertiesWithFfmpeg(): bool;

    public function hasLimitedMimeTypes(): bool;

    public function getLimitedMimeTypes(): array;

    public function getRegisterConversionsUsing(): array;

    public function getMaxSize(): ?int;

    public function getMinSize(): ?int;
}
