@props(['item'])
@php
    $nodeLevel = $item['level'];
    $nodePath = $item['path'];
    $hasChildren = $item['is_directory'] && !$item['is_directory_empty'];
@endphp
<li 
    tabindex="@js($nodeLevel)" 
    data-unique-key="{{ $nodePath }}" 
    data-treenode
    class="cursor-pointer"
>
    <div x-on:click="await toggleItem('{{ $nodePath }}', @js($nodeLevel))" class="node w-full inline-flex items-center gap-1 rounded-md hover:bg-gray-100 px-1 py-1.5">
        <span class="w-4">
            @if ($hasChildren)
                <x-icon x-show="isExpanded('{{ $nodePath }}')" name="heroicon-o-chevron-down" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                <x-icon x-show="!isExpanded('{{ $nodePath }}')" name="heroicon-o-chevron-right" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
            @endif
        </span>
        <span class="flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-200">{{ $item['name'] ?? null }}</span>
    </div>
    <ul x-show="isExpanded('{{ $nodePath }}')" x-transition 
        {{ $hasChildren ? 'data-subtree' : ''}} 
        @style([
        'padding-left:' . (18 + $nodeLevel) . 'px',
        ])
    >
        @foreach ($item['children'] ?? [] as $child)
            <x-inspirecms-support::file-explorer.item  
                :item="$child" 
            />
        @endforeach
    </ul>
</li>