<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Livewire\Attributes\On;

trait CanSelectFileItem
{
    public ?string $fileExplorerSelectedPath = null;

    public array $cachedFileExplorerItems = [];

    #[On('getFilesFrom')]
    public function getFilesFromDirectory(string $path, int $level)
    {
        if (! $this->getFileExplorer()->checkPermission($path)) {
            $this->getPermissionDeniedNotification()->send();

            return;
        }

        if (isset($this->cachedFileExplorerItems[$path])) {
            return;
        }

        $children = $this->getFileExplorer()->getFileDataCollection($path, $level)->toArray();

        $this->cachedFileExplorerItems[$path] = $children;
    }

    #[On('selectFile')]
    public function selectFile($path)
    {
        // Add a guard clause to prevent re-selection of the same file
        if ($this->fileExplorerSelectedPath && $this->fileExplorerSelectedPath === $path) {

            $this->dispatch('selectFileExplorerItem', $path);

            return;
        }

        // A guard for avoiding selecting a directory
        if ($this->getFileExplorer()->isSelectedItemDirectory($path)) {
            return;
        }

        $this->fileExplorerSelectedPath = $path;

        $this->dispatch('selectFileExplorerItem', $path);
    }

    public function getFileContent($path)
    {
        return $this->getFileExplorer()->getFileContent($path);
    }

    public function getGroupedNodeItems()
    {
        $fileExplorer = $this->getFileExplorer();

        if (empty($this->cachedFileExplorerItems)) {
            $rootPath = $fileExplorer->getRootPath();
            $items = $this->getFileExplorer()->getFileDataCollection($rootPath, 0)->toArray();
            $this->cachedFileExplorerItems[$rootPath] = $items;
        }

        // Convert the items array as node tree items array
        $nodes = [];
        $groupByDepth = collect($this->cachedFileExplorerItems)->flatten(1)->groupBy('level');
        foreach ($groupByDepth as $depth => $flattenItems) {
            if ($depth === 0) {
                $nodes = collect($flattenItems)->map(fn ($item) => array_merge($item, ['children' => []]))->toArray();

                continue;
            }

            $groupByParentKey = collect($flattenItems)
                ->groupBy(fn ($item) => str($item['path'])->beforeLast('/')->toString())
                ->toArray();
            foreach ($groupByParentKey as $parentKey => $items) {
                $fileExplorer->attachItemsToNodes($parentKey, $items, $nodes);
            }

        }

        return $nodes;
    }
}
