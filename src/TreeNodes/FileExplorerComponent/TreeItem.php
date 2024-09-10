<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\FileExplorerComponent;

use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Livewire\Component;
use SolutionForest\InspireCms\Support\Data\FileExploreItem;

class TreeItem extends Component
{
    public FileExploreItem $item;

    public bool $isExpanded = false;

    public $children = null;

    public $isExpandLoading = false;

    public function mount(FileExploreItem $item)
    {
        $this->item = $item;
    }

    #[On('toggleExpand')]
    public function toggleExpand()
    {
        if ($this->item->isDirectory) {
            $this->isExpanded = ! $this->isExpanded;
            if ($this->isExpanded && $this->children === null) {
                $this->isExpandLoading = true;
                $this->dispatch('getChildren', path: $this->item->path, level: $this->item->level + 1);
            }
        } else {
            $this->dispatch('selectFile', path: $this->item->path);
        }
    }

    #[On('childrenLoaded')]
    public function onChildrenLoaded($children, $path)
    {
        if ($path === $this->item->path) {
            $this->isExpandLoading = false;
            if (empty($children)) {
                $this->getLoadingChildrenFailedNotification()->send();

                return;
            }

            $this->children = collect($children)->map(function ($child) {
                return new FileExploreItem(
                    $child['idx'],
                    $child['name'],
                    $child['isDirectory'],
                    $child['isDirectoryEmpty'],
                    $child['isFile'],
                    $child['ext'],
                    $child['level'],
                    $child['path']
                );
            });
        }
    }

    public function hasChildren(): bool
    {
        return $this->item->isDirectory && ! $this->item->isDirectoryEmpty;
    }

    public function getIcon(): string
    {
        if ($this->item->isDirectory) {
            return $this->isExpanded ? 'heroicon-o-folder-open' : 'heroicon-o-folder';
        }

        return 'heroicon-o-document';
    }

    public function render()
    {
        return view('inspirecms-support::file-explorer.tree-item');
    }

    protected function getLoadingChildrenFailedNotification()
    {
        return Notification::make()
            ->title(__('inspirecms-support::notification.loading_children_failed.title'))
            ->body(__('inspirecms-support::notification.loading_children_failed.body'))
            ->danger();
    }
}
