@props(['mediaItem', 'actions' => [], 'disabled' => false])
@php
    $isFolder = $mediaItem->isFolder();
@endphp
    
<div 
    {{ $attributes
        ->class([
            'browser-item',
        ])
    }}
>
    <div class="header">
        <input 
            wire:model.live="selectedMediaId" 
            id="media-item-{{ $mediaItem->getKey() }}"
            value=@js($mediaItem->getKey())
            type="checkbox" 
            @disabled($disabled)
        >
        <div class="actions">
            <x-inspirecms-support::media-library.actions
                :actions="$actions" 
                :media-item="$mediaItem"
            />
        </div>
    </div>

    <label for="media-item-{{ $mediaItem->getKey() }}" class="cursor-pointer w-full flex flex-col gap-y-1 items-center justify-center">
        <div class="thumbnail-ctn">
            @if ($mediaItem->isImage())
                <img loading="lazy" src="{{ $mediaItem->getThumbnailUrl() }}" alt="{{ $mediaItem->getKey() }}" />
            @else
                <x-inspirecms-support::media-library.thumbnail-icon 
                    :icon="$mediaItem->getThumbnail()" 
                />
            @endif
        </div>
        <div class="flex flex-col items-center justify-center gap-y-1 overflow-hidden w-full">
            <span class="w-full text-center truncate">{{ $mediaItem->title }}</span>
            @if ($mediaItem->isFolder())
                <span class="w-full text-center text-gray-400 text-xs">{{ $mediaItem->children_count }} items</span>
            @endif
        </div>
    </label>
</div>