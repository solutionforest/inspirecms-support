@props(['livewireKey', 'mediaItem', 'actions' => [], 'selectable' => true, 'isDraggable' => true])
@php
    $isFolder = $mediaItem->isFolder();
@endphp
    
<div 
    @if ($isDraggable)
        ax-load
        ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('media-draggable-item-component', 'solution-forest/inspirecms-support') }}"
        x-ignore
        x-data="mediaDraggableItemComponent({
            livewireKey: @js($livewireKey),
        })"
        draggable="true"
        data-draggable-id=@js($mediaItem->getKey())
        data-draggable-type="{{ ($isFolder ? 'folder' : 'media') }}"
        x-bind:class="{
            'drag-and-drop__item--dragging': dragging
        }"
        x-on:dragstart.self="onDragStart($event)"
        x-on:dragend="onDragEnd($event)"
        x-on:dragover.prevent="onDragOver($event)"
        x-on:dragleave.prevent="onDragLeave($event)"
        x-on:drop.prevent="onDrop($event)"
    @endif
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
            @disabled(!$selectable)
        >
        <div 
            class="actions"
        >
            <x-inspirecms-support::media-library.actions
                :actions="$actions" 
                :media-item="$mediaItem"
            />
        </div>
    </div>

    <div class="main" wire:click="toggleMedia('{{ $mediaItem->getKey() }}', '{{ $isFolder }}')">
        <div class="thumbnail-ctn">
            @if ($mediaItem->isImage())
                <img loading="lazy" src="{{ $mediaItem->getThumbnailUrl() }}" alt="{{ $mediaItem->getKey() }}" />
            @else
                @if ($isFolder)
                    <x-inspirecms-support::media-library.thumbnail-icon 
                        :icon="$mediaItem->getThumbnail()" 
                        class="icon-folder"
                    />
                    <x-inspirecms-support::media-library.thumbnail-icon 
                        :icon="$mediaItem->getActiveThumbnail()" 
                        class="icon-folder-active"
                    />
                @else
                    <x-inspirecms-support::media-library.thumbnail-icon 
                        :icon="$mediaItem->getThumbnail()" 
                    />
                @endif
            @endif
        </div>
        <div class="title-ctn">
            <span class="title">{{ $mediaItem->title }}</span>
            @if ($mediaItem->isFolder())
                <span class="description">
                    {{ __('inspirecms-support::media-library.messages.total_xxx_items', ['count' => $mediaItem->children_count]) }}
                </span>
            @endif
        </div>
    </div>
</div>