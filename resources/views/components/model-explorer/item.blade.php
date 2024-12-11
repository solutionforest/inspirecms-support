@props(['item', 'modelExplorer', 'selectedKey' => null, 'translatable' => false, 'translatableLocale' => null, 'spaMode' => false])
@php
    use Illuminate\Support\Arr;

    $nodeDepth = $item['depth'];
    $nodeKey = $item['key'];
    $hasChildren = $item['hasChildren'];

    $itemLabel = $item['label'] ?? null;

    if ($translatable == true && !blank($translatableLocale) && $itemLabel && is_array($itemLabel)) {
        $itemLabel = $itemLabel[$translatableLocale] ?? $item['fallbackLabel'] ?? null;
    } else if (is_array($itemLabel)) {
        $itemLabel = reset($itemLabel);
    }

    $actions = $modelExplorer->getVisibleActionsForItem($item);

    $hasIcon = filled($item['icon'] ?? null);

    $labelClasses = Arr::toCssClasses([
        'text-gray-700 dark:text-gray-200',
        'text-md font-bold' => $nodeDepth < 0,
        'text-sm font-medium' => $nodeDepth >= 0,
        ... collect($item['extraAttributes']['class'] ?? [])->filter()->all(),
    ]);
    
@endphp
<li tabindex="@js($nodeDepth)" 
    data-unique-key="{{ $nodeKey }}" 
    data-treenode
    class="cursor-pointer select-none"
>
    <div @class([
        'node w-full inline-flex items-center gap-1 px-1 py-1.5 hover:bg-gray-100 dark:hover:bg-white/5',
        'h-11 shadow' => $nodeDepth < 0,
        'rounded-md' => $nodeDepth >= 0,
        'bg-gray-100 dark:bg-white/5' => $selectedKey === $nodeKey,
    ])>
        <span class="w-4" 
            x-on:click="await toggleItem('{{ $nodeKey }}', @js($nodeDepth))"
        >
            @if ($hasChildren)
                <x-icon x-cloak x-show="isExpanded('{{ $nodeKey }}')" name="heroicon-o-chevron-down" class="text-gray-800 dark:text-gray-100" />
                <x-icon x-show="!isExpanded('{{ $nodeKey }}')" name="heroicon-o-chevron-right" class="text-gray-800 dark:text-gray-100" />
            @endif
        </span>
        @if (filled($item['link'] ?? null))
            <a href="{{ $item['link'] }}" 
                @class([
                    $labelClasses,
                    'flex-1 flex gap-x-2 truncate',
                ])
                x-on:click="selectItem('{{ $nodeKey }}')"
                @if ($spaMode)
                    wire:navigate
                @endif
            >
                @if ($hasIcon)
                    <x-icon :name="$item['icon']" class="w-4 h-4 text-gray-400 dark:text-gray-200" />
                @endif
                <span>
                    {{ $itemLabel }}
                </span>
            </a>
            
        @else
            <span class="flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-200"
                @class([
                    $labelClasses,
                    'flex-1 truncate',
                ])
                x-on:click="selectItem('{{ $nodeKey }}')"
            >
                {{ $itemLabel }}
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
    <ul x-show="isExpanded('{{ $nodeKey }}')" x-transition {{ $hasChildren ? 'data-subtree' : ''}} 
        @style([
            'padding-left:' . (15 + $nodeDepth) . 'px',
        ])
    >
        @foreach ($item['children'] ?? [] as $child)
            <x-inspirecms-support::model-explorer.item  
                :item="$child" 
                :selectedKey="$selectedKey"
                :model-explorer="$modelExplorer"
                :translatable="$translatable"
                :translatable-locale="$translatableLocale"
                :spa-mode="$spaMode"
            />
        @endforeach
    </ul>
</li>