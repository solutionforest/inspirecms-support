@php
    $maxDepth ??= -1;
    $maxVisibleDepth ??= 20;
    $allowDragDrop ??= false;
    $isSearchable ??= false;

    $toolbarActions ??= [];
    $toolbarActions = array_filter($toolbarActions, fn ($action) => $action->isVisible());

    $hasToolbarActions = ($showToolbarActions ?? false) && count($toolbarActions) > 0;
    $hasNodeActions = ($showNodeActions ?? false);
@endphp
<div class="tree-view-ctn"
    x-data="TreeView({
        data: $wire.entangle('nodes'),
        maxDepth: @js($maxDepth),
        maxVisibleDepth: @js($maxVisibleDepth),
        enableKeyboardNav: false,
        allowDragDrop: @js($allowDragDrop),
        allowCrossCategory: false,
        highlightSearch: true,
        onSearch: (query, results, tree) => {
            tree.expandSearchResults();
        },
    })"
>
    @if ($isSearchable)
        <x-filament::input.wrapper
            prefix-icon="heroicon-m-magnifying-glass"
            prefix-icon-alias="panels::global-search.field"
            inline-prefix
            class="search-ctn"
        >
            <x-filament::input
                autocomplete="off"
                inline-prefix
                maxlength="1000"
                type="search"
                x-model="searchQuery" 
                placeholder="Search nodes..."
                class="search-input" 
            />
        </x-filament::input.wrapper>
    @endif

    @if ($hasToolbarActions)
        <x-filament::actions :actions="$toolbarActions"/>
    @endif

    <div class="tree-ctn">
        <template x-for="(node, index) in treeData" :key="node.id + '-' + index">
            <x-inspirecms-support::tree-node.recursive-node
                :level="1" 
                nodeVariable="node"
                indexVariable="index"
                parentId="null"
                :maxDepth="$maxDepth"
                :hasActions="$hasNodeActions"
                :livewire="$this"
            />
        </template>
    </div>

    <x-filament-actions::modals />
</div>