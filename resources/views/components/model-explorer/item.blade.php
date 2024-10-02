@props(['item'])
@php
    $nodeDepth = $item['depth'];
    $nodeKey = $item['key'];
    $hasChildren = $item['hasChildren'];
@endphp
<li x-data="{
    itemKey: @js($nodeKey),
    depth: @js($nodeDepth),
    hasChildren: @js($hasChildren),
}" tabindex="@js($nodeDepth)" data-unique-key="{{ $nodeKey }}" data-treenode
    class="cursor-pointer"
>
    <div @click="await toggleItem(itemKey, depth)" class="node w-full inline-flex items-center gap-1 rounded-md hover:bg-gray-100 px-1 py-1.5">
        <span class="w-4">
            <x-icon x-show="hasChildren && isExpanded(itemKey)" name="heroicon-o-chevron-down" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
            <x-icon x-show="hasChildren && !isExpanded(itemKey)" name="heroicon-o-chevron-right" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
        </span>
        <span class="flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-200">{{ $item['label'] ?? null }}</span>
    </div>
    <ul x-show="isExpanded(itemKey)" x-transition {{ $hasChildren ? 'data-subtree' : ''}} @style([
        'padding-left:' . (18 + $nodeDepth) . 'px',
    ])>
        @foreach ($item['children'] ?? [] as $child)
            <x-inspirecms-support::model-explorer.item  
                :item="$child" 
            />
        @endforeach
    </ul>
</li>