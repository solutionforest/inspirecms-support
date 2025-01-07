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

    <div class="main" wire:click="toggleMedia('{{ $mediaItem->getKey() }}')">
        <div class="thumbnail-ctn">
            @if ($mediaItem->isImage())
                <img loading="lazy" src="{{ $mediaItem->getThumbnailUrl() }}" alt="{{ $mediaItem->getKey() }}" />
            @else
                <x-inspirecms-support::media-library.thumbnail-icon 
                    :icon="$mediaItem->getThumbnail()" 
                />
            @endif
        </div>
        <div class="title-ctn">
            <span class="title">{{ $mediaItem->title }}</span>
            @if ($mediaItem->isFolder())
                <span class="description">{{ $mediaItem->children_count }} items</span>
            @endif
        </div>
    </div>
</div>