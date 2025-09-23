@php
    $maxDepth ??= -1;
    $maxVisibleDepth ??= 20;
    $allowDragDrop ??= false;
    $searchable ??= false;

    $toolbarActions ??= [];
    $toolbarActions = array_filter($toolbarActions, fn ($action) => $action->isVisible());

    if (($showToolbarActions ?? false) != true) {
        $toolbarActions = [];
    }
    $hasNodeActions = ($showNodeActions ?? false);
@endphp
<div>
    
    <x-inspirecms-support::tree-node.recursive-node
        :livewire="$this"
        :maxDepth="$maxDepth"
        :maxVisibleDepth="$maxVisibleDepth"
        :allowDragDrop="$allowDragDrop"
        :isSearchable="$searchable"
        :toolbarActions="$toolbarActions"
        :hasNodeActions="$hasNodeActions"
    />

    <x-filament-actions::modals />
</div>