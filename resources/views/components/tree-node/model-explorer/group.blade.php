@props([
    'item', 
    'modelExplorer', 
    'translatable' => false, 
    'translatableLocale' => null, 
    'spaMode' => false,
    'isDisabled' => false,
])

@php
    $nodeDepth = $item['depth'];
    $nodeKey = $item['key'];
    $hasChildren = $item['hasChildren'];
@endphp

<li tabindex="{{ $nodeDepth }}" 
    data-unique-key="{{ $nodeKey }}" 
    data-treenode
    role="treeitem"
    class="select-none"
>
    <x-inspirecms-support::tree-node.model-explorer.item
        :item="$item" 
        :model-explorer="$modelExplorer"
        :translatable="$translatable"
        :translatable-locale="$translatableLocale"
        :spa-mode="$spaMode"
        :is-disabled="$isDisabled"
    />
    
    <ul role="group"
        x-show="isExpanded('{{ $nodeKey }}')" 
        x-transition 
    >
        <x-inspirecms-support::tree-node.model-explorer.groups
            tag="div"
            :items="$item['children'] ?? []" 
            :model-explorer="$modelExplorer"
            :translatable="$translatable"
            :translatable-locale="$translatableLocale"
            :spa-mode="$spaMode"
            :is-disabled="$isDisabled"
        />
    </ul>
</li>