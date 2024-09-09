<?php

namespace SolutionForest\InspireCms\Support\Data;

use Livewire\Wireable;

class FileExploreItem implements Wireable
{
    public function __construct(
        public int $idx,
        public string $name,
        public bool $isDirectory,
        public bool $isDirectoryEmpty,
        public bool $isFile,
        public ?string $ext,
        public int $level,
        public string $path,
    ) {}

    public function toLivewire()
    {
        return [
            'idx' => $this->idx,
            'name' => $this->name,
            'is_directory' => $this->isDirectory,
            'is_directory_empty' => $this->isDirectoryEmpty,
            'is_file' => $this->isFile,
            'ext' => $this->ext,
            'level' => $this->level,
            'path' => $this->path,
        ];
    }

    public static function fromLivewire($value)
    {
        return new static(
            $value['idx'],
            $value['name'],
            $value['is_directory'],
            $value['is_directory_empty'],
            $value['is_file'],
            $value['ext'],
            $value['level'],
            $value['path'],
        );
    }
}
