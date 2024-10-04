@props(['item', 'actions' => [], 'selectedKey' => null])
@php
    $nodeDepth = $item['depth'];
    $nodeKey = $item['key'];
    $hasChildren = $item['hasChildren'];
    $actions = collect($actions)->map(function ($action) use ($item) {
        return $action->arguments([
            'key' => $item['key'],
        ]);
    })->all();
@endphp
<li tabindex="@js($nodeDepth)" 
    data-unique-key="{{ $nodeKey }}" 
    data-treenode
    class="cursor-pointer select-none"
>
    <div @class([
        'node w-full inline-flex items-center gap-1 rounded-md hover:bg-gray-100 dark:hover:bg-white/5 px-1 py-1.5',
        'bg-gray-100 dark:bg-white/5' => $selectedKey === $nodeKey,
    ])>
        <span class="w-4" 
            x-on:click="await toggleItem('{{ $nodeKey }}', @js($nodeDepth))"
        >
            @if ($hasChildren)
                <x-icon x-cloak x-show="isExpanded('{{ $nodeKey }}')" name="heroicon-o-chevron-down" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                <x-icon x-show="!isExpanded('{{ $nodeKey }}')" name="heroicon-o-chevron-right" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
            @else
                @if (filled($item['icon'] ?? null))
                    <x-icon :name="$item['icon']" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                @endif
            @endif
        </span>
        @if (filled($item['link'] ?? null))
            <a href="{{ $item['link'] }}" class="flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-200"
                x-on:click="selectItem('{{ $nodeKey }}')"
            >
                {{ $item['label'] ?? null }}
            </a>
            
        @else
            <span class="flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-200"
                x-on:click="selectItem('{{ $nodeKey }}')"
            >
                {{ $item['label'] ?? null }}
            </span>
        @endif
        @if (count($actions))
            <div>
                <x-filament-actions::group
                    :actions="$actions"
                    label="Actions"
                    icon="heroicon-m-ellipsis-vertical"
                    color="primary"
                    size="md"
                    dropdown-placement="bottom-start"
                />

                <x-filament-actions::modals />
            </div>
        @endif
    </div>
    <ul x-show="isExpanded('{{ $nodeKey }}')" x-transition {{ $hasChildren ? 'data-subtree' : ''}} @style([
        'padding-left:' . (18 + $nodeDepth) . 'px',
    ])>
        @foreach ($item['children'] ?? [] as $child)
            <x-inspirecms-support::model-explorer.item  
                :item="$child" 
                :selectedKey="$selectedKey"
                :actions="$actions"
            />
        @endforeach
    </ul>
</li>