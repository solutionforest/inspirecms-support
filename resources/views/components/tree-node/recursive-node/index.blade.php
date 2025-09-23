@props([
    'livewire',
    'maxDepth' => -1, // -1 means unlimited
    'maxVisibleDepth' => 20,
    'allowDragDrop' => false,
    'isSearchable' => false,
    'toolbarActions' => [],
    'hasNodeActions' => false,
])

<div {{ $attributes->class([
        'recursive-tree-node',
        'searchable-tree-node' => $isSearchable,
        'drag-drop-tree-node' => $allowDragDrop,
    ]) }}
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

    @if (count($toolbarActions) > 0)
        <x-filament::actions :actions="$toolbarActions" class="tree-toolbar"/>
    @endif

    <div class="tree-node-items">
        <template x-for="(node, index) in treeData" :key="node.id + '-' + index">
            <x-inspirecms-support::tree-node.recursive-node.item
                :level="1" 
                nodeVariable="node"
                indexVariable="index"
                parentId="null"
                :maxDepth="$maxDepth"
                :hasActions="$hasNodeActions"
                :livewire="$livewire"
            />
        </template>
    </div>
</div>