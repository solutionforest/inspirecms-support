<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use SolutionForest\InspireCms\Support\TreeNodes\FileExplorer;

trait InteractsWithFileExplorer
{
    use CanSelectFileItem;

    protected FileExplorer $fileExplorer;

    public function bootInteractsWithFileExplorer()
    {

        $this->fileExplorer = \Filament\Actions\Action::configureUsing(
            \Closure::fromCallable([$this, 'configureSelectedFileItemFormAction']),
            fn () => $this->fileExplorer($this->makeFileExplorer())
        );

        $this->cacheForm('selectedFileItemForm', $this->getSelectedFileItemForm());
    }

    public function fileExplorer(FileExplorer $fileExplorer): FileExplorer
    {
        return $fileExplorer
            ->diskName($this->getDiskName())
            ->directory($this->getDirectory());
    }

    public function getFileExplorer(): FileExplorer
    {
        return $this->fileExplorer;
    }

    protected function makeFileExplorer(): FileExplorer
    {
        return FileExplorer::make($this);
    }

    public function getDiskName(): ?string
    {
        return null;
    }

    public function getDirectory(): ?string
    {
        return null;
    }
}
