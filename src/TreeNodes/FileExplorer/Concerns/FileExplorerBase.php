<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\FileExplorer\Concerns;

use Illuminate\Support\Facades\Storage;

trait FileExplorerBase
{
    protected ?string $diskName = null;

    protected ?string $directory = null;

    public function diskName(?string $diskName): self
    {
        $this->diskName = $diskName;

        return $this;
    }

    public function directory(?string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    public function getDiskName(): ?string
    {
        return $this->diskName;
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    /**
     * @return ?\Illuminate\Contracts\Filesystem\Filesystem
     *
     * @throws \Exception
     */
    public function getDisk()
    {
        $diskName = $this->getDiskName();

        if (empty($diskName)) {
            return null;
        }

        try {

            if (! config("filesystems.disks.{$diskName}")) {
                throw new \Exception("Disk '{$diskName}' is not configured in filesystems.php");
            }

            return Storage::disk($diskName);

        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
