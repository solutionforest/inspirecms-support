@props([
    'item', 
    'modelExplorer', 
    'translatable' => false, 
    'translatableLocale' => null, 
    'spaMode' => false,
    'isDisabled' => false,
])
@php
    use Illuminate\Support\Arr;

    $nodeDepth = $item['depth'];
    $nodeKey = $item['key'];
    $hasChildren = $item['hasChildren'];

    $itemTitle = $modelExplorer->getTitleForItem($item, ($translatable == true && !blank($translatableLocale)) ? $translatableLocale : null);

    $itemDescription = $item['description'] ?? null;
    $hasDescription = filled($itemDescription);

    if ($item['isDisabled'] ?? false) {
        $isDisabled = true;
    }

    $actions = $modelExplorer->getVisibleActionsForItem($item);

    $hasIcon = filled($item['icon'] ?? null);

    $titleClasses = Arr::toCssClasses([
        'w-full truncate',
        'text-gray-700 dark:text-gray-200' => ! $isDisabled,
        'text-gray-400 dark:text-gray-400' => $isDisabled,
        'text-md font-bold' => $nodeDepth < 0,
        'text-sm font-medium' => $nodeDepth >= 0,
        ... collect($item['extraAttributes']['title']['class'] ?? [])->filter()->all(),
    ]);
    $titleStyles = Arr::toCssStyles([
        ... collect($item['extraAttributes']['title']['style'] ?? [])->filter()->all(),
    ]);

    $descriptionClasses = Arr::toCssClasses([
        'w-full truncate',
        'text-gray-500 dark:text-gray-400 text-xs ',
        ... collect($item['extraAttributes']['description']['class'] ?? [])->filter()->all(),
    ]);
    $descriptionStyles = Arr::toCssStyles([
        ... collect($item['extraAttributes']['description']['style'] ?? [])->filter()->all(),
    ]);
@endphp

<div 
    @class([
        'node w-full inline-flex items-center gap-1 px-1 hover:bg-gray-100 dark:hover:bg-white/5',
        'rounded-md' => $nodeDepth >= 0,
        ... collect($item['extraAttributes']['ctn']['class'] ?? [])->filter()->all(),
    ])
    x-bind:class="{
        'bg-gray-100 dark:bg-white/5': isSelected('{{ $nodeKey }}')
    }"
>
    <span class="w-4" 
        @if ($hasChildren)
            x-on:click="toggleItem('{{ $nodeKey }}')"
        @endif
    >
        @if ($hasChildren)
            <x-icon x-cloak x-show="isExpanded('{{ $nodeKey }}')" name="heroicon-o-chevron-down" class="text-gray-800 dark:text-gray-100" />
            <x-icon x-show="!isExpanded('{{ $nodeKey }}')" name="heroicon-o-chevron-right" class="text-gray-800 dark:text-gray-100" />
        @endif
    </span>

    @if (filled($item['link'] ?? null))
        <a href="{{ $item['link'] }}" 
            class="flex-1 flex items-center gap-x-2 truncate py-1.5"
            @if ($spaMode)
                wire:navigate
            @endif
        >
            @if ($hasIcon)
                <x-icon :name="$item['icon']" class="w-4 h-4 text-gray-400 dark:text-gray-200" />
            @endif

            <span @class([
                'flex-1',
                'inline-flex flex-col' => $hasDescription,
            ])>
                <span @class([$titleClasses]) @style([$titleStyles])>
                    {{ $itemTitle }}
                </span>
    
                @if ($hasDescription)
                    <span @class([$descriptionClasses]) @style([$descriptionStyles])>
                        {{ $itemDescription }}
                    </span>
                @endif
            </span>
        </a>
        
    @else
        <div @class([
            'flex-1 truncate py-1.5',
            'inline-flex flex-col' => $hasDescription,
        ]) 
            @style([
                'cursor: not-allowed;' => $isDisabled,
            ])
            @if (!$isDisabled)
                x-on:click="selectItem('{{ $nodeKey }}')"
            @endif
        >
            <span @class([$titleClasses]) @style([$titleStyles])>
                {{ $itemTitle }}
            </span>

            @if ($hasDescription)
                <span @class([$descriptionClasses]) @style([$descriptionStyles])>
                    {{ $itemDescription }}
                </span>
            @endif
        </div>
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
        </div>
    @endif

</div>