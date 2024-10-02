@props(['item', 'selectedKey' => null])
@php
    $nodeDepth = $item['depth'];
    $nodeKey = $item['key'];
    $hasChildren = $item['hasChildren'];
@endphp
<li tabindex="@js($nodeDepth)" 
    data-unique-key="{{ $nodeKey }}" 
    data-treenode
    class="cursor-pointer"
>
    <div @class([
        'node w-full inline-flex items-center gap-1 rounded-md hover:bg-gray-100 dark:hover:bg-white/5 px-1 py-1.5',
        'bg-gray-100 dark:bg-white/5' => $selectedKey === $nodeKey,
    ])>
        <span class="w-4" 
            x-on:click="await toggleItem('{{ $nodeKey }}', @js($nodeDepth))"
        >
            @if ($hasChildren)
                <x-icon x-show="isExpanded('{{ $nodeKey }}')" name="heroicon-o-chevron-down" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                <x-icon x-show="!isExpanded('{{ $nodeKey }}')" name="heroicon-o-chevron-right" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
            @endif
        </span>
        <span class="flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-200"
            x-on:click="selectItem('{{ $nodeKey }}')"
        >
            {{ $item['label'] ?? null }}
        </span>
    </div>
    <ul x-show="isExpanded('{{ $nodeKey }}')" x-transition {{ $hasChildren ? 'data-subtree' : ''}} @style([
        'padding-left:' . (18 + $nodeDepth) . 'px',
    ])>
        @foreach ($item['children'] ?? [] as $child)
            <x-inspirecms-support::model-explorer.item  
                :item="$child" 
                :selectedKey="$selectedKey"
            />
        @endforeach
    </ul>
</li>