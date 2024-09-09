<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\ModelExplorerComponent;

use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Livewire\Component;

class TreeItem extends Component
{
    public Model $item;

    public bool $isExpanded = false;

    public array $children = [];

    public bool $hasChildren = false;

    public $isExpandLoading = false;

    public function mount(Model $item)
    {
        $this->item = $item;
        $this->dispatch('getChildren', parentKey: $this->item->getKey());
    }

    #[On('toggleExpand')]
    public function toggleExpand()
    {
        $this->isExpanded = ! $this->isExpanded;
        // if ($this->item->isDirectory) {
        //     if ($this->isExpanded && $this->children === null) {
        //         $this->isExpandLoading = true;
        //         $this->dispatch('getChildren', path: $this->item->path, level: $this->item->level + 1);
        //     }
        // } else {
        //     $this->dispatch('selectFile', path: $this->item->path);
        // }
    }

    #[On('childrenLoaded')]
    public function onChildrenLoaded($children)
    {
        $this->children = $children;
        $this->hasChildren = count($children) > 0;
    }

    public function render()
    {
        return view('inspirecms-support::model-explorer.tree-item');
    }
}
