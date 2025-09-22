@php
    $toolbarActions ??= [];
    $navigationHeaderActions ??= [];
    
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
        :hasNodeActions="$showNodeActions"
        :toolbarActions="$toolbarActions"
        :navigationHeaderActions="$navigationHeaderActions"
        :showNavigationHeader="$showNavigationHeader"
        :enableSelection="$enableSelection"
        :multipleSelection="$multipleSelection"
        :enableNodeUrls="$enableNodeUrls"
        :maxSelections="$maxSelections"
        :homeButtonText="$homeButtonText"
        :indexUrl="$indexUrl"
    />
    
    <x-filament-actions::modals />
</div>
