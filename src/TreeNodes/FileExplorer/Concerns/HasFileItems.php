<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\FileExplorer\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HasFileItems
{
    /**
     * @return Collection
     */
    public function getFileDataCollection(string $path, int $level)
    {
        $dir = $this->getFullPath($path);

        if (! is_dir($dir)) {
            return collect();
        }

        $files = collect(scandir($dir))
            ->where(fn ($filename) => ! in_array($filename, ['.', '..']));

        return $files->map(function ($filename, $index) use ($path, $level) {
            $filePath = Str::of($path)->append('/')->append($filename)->toString();

            return $this->parseAsItem($filePath, $level, $index);
        });
    }

    private function parseAsItem(string $path, int $level, int $index): array
    {
        $fullPath = $this->getFullPath($path);
        $isDir = is_dir($fullPath);

        return [
            'idx' => $index,
            'name' => basename($fullPath),
            'is_directory' => $isDir,
            'is_directory_empty' => $isDir && count(scandir($fullPath)) <= 2,
            'is_file' => ! $isDir,
            'ext' => $isDir ? null : pathinfo($fullPath, PATHINFO_EXTENSION),
            'level' => $level,
            'path' => $path,
        ];
    }

    public function checkPermission(string $path): bool
    {
        try {

            // Add a timeout to the exists check
            if ($disk = $this->getDisk()) {

                return rescue(function () use ($disk, $path) {
                    return $disk->exists($path);
                }, false, 5); // 5 seconds timeout
            }

            return rescue(function () use ($path) {
                return $this->getFullPath($path);
            }, false, 5); // 5 seconds timeout

        } catch (\Throwable $th) {
            return false;
        }
    }

    public function getFullPath(string $path): string
    {
        if ($disk = $this->getDisk()) {
            return $disk->path($path);
        }

        // Using directory if diskName not set
        $directory = $this->getDirectory();
        if (empty($directory)) {
            throw new \InvalidArgumentException('Either diskName or directory must be set.');
        }

        $trimmedPath = (string) str($path)->trim()->rtrim('/');

        if (str($trimmedPath)->startsWith($directory)) {
            return $trimmedPath;
        }

        return str($directory)
            ->trim()->rtrim('/')
            ->finish('/')
            ->finish($trimmedPath);
    }

    public function getRootPath(): ?string
    {
        return (string) str($this->getDirectory() ?? '')->trim()->rtrim('/');
    }
    
    public function getNodeItemKey(array $item): mixed
    {
        $path = data_get($item, 'path') ?? '';

        return $this->getFullPath($path);
    }
}
