@php
    $toolbarActions ??= [];
    $nodes ??= [];
    $enableSelection ??= false;
    $multipleSelection ??= true;
    $enableNodeUrls ??= true;
    $maxSelections ??= null;
@endphp

<div>
    <x-inspirecms-support::tree-node.service-side-tree
        :nodes="$nodes"
        :livewire="$this"
        :toolbarActions="$this->showToolbarActions ? $toolbarActions : []"
        :hasNodeActions="$this->showNodeActions"
        :enableSelection="$enableSelection"
        :multipleSelection="$multipleSelection"
        :enableNodeUrls="$enableNodeUrls"
        :maxSelections="$maxSelections"
    />
    
    <x-filament-actions::modals />
</div>
