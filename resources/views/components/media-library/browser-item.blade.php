@props(['livewireKey', 'mediaItem', 'actions' => [], 'selectable' => true, 'isDraggable' => true])
@php
    $isFolder = $mediaItem->isFolder();
@endphp
    
<div
    @if ($isDraggable) 
        x-data="mediaItem({
            livewireKey: @js($livewireKey),
        })"
        draggable="true"
        data-draggable-id=@js($mediaItem->getKey())
        data-draggable-type="{{ ($isFolder ? 'folder' : 'media') }}"
        x-on:dragstart="onDragStart($event)"
        x-on:dragend="onDragEnd($event)"
        x-on:dragover.prevent="onDragOver($event)"
        x-on:dragleave="onDragLeave($event)"
        x-on:drop.prevent="onDrop($event)"
        :class="{
            'drag-over': isDragOver,
            'dragging': isDragging
        }"
    @endif
    {{ $attributes
        ->class([
            'browser-item relative cursor-pointer group',
        ])
    }}
>
    @if ($isFolder && $isDraggable)
        <!-- Drag Overlay -->
        <div 
            x-show="isDragOver" x-cloak
            class="drag-overlay"
            @style([\Filament\Support\get_color_css_variables('primary', [200, 400])])
        ></div>
    @endif
    
    <div class="pb-2 flex items-center justify-between">
        <!-- Selection Checkbox -->
        <input 
            type="checkbox" 
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

    <!-- Item Content -->
    <div class="item-content"
    >
        <!-- Thumbnail -->
        <div wire:click="toggleMedia('{{ $mediaItem->getKey() }}', '{{ $isFolder }}')"
            class="thumbnail-ctn mb-2 flex justify-center"
        >
            @if ($mediaItem->isFolder())
                <div class="relative" 
                    @style([\Filament\Support\get_color_css_variables('warning', [400, 500, 600])])
                >
                    @if ($isDraggable)
                        <svg x-show="!isDragOver" class="folder-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                        </svg>
                        <svg x-show="isDragOver" x-cloak class="folder-icon active" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1H8a3 3 0 00-3 3v1.5a1.5 1.5 0 01-3 0V6z" clip-rule="evenodd"></path>
                            <path d="M6 12a2 2 0 012-2h8a2 2 0 012 2v2a2 2 0 01-2 2H8a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    @else
                        <svg class="folder-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                        </svg>
                    @endif
                </div>

            @elseif ($mediaItem->isImage())
                <img loading="lazy" 
                    alt="{{ $mediaItem->getKey() }}" 
                    class="object-cover rounded"
                    x-data="{ src: '{{ $mediaItem->getThumbnailUrl() }}?' + Date.now() }"
                    :src="src" 
                    src="{{ $mediaItem->getThumbnailUrl() }}"
                    x-on:media-thumb-updated.window="(event) => {
                        const updatedId = (Array.isArray(event.detail) ? event.detail[0]?.id : event.detail?.id) || null;
                        if (!updatedId) {
                            return;
                        }
                        if (updatedId === '{{ $mediaItem->getKey() }}') {
                            src = '{{ $mediaItem->getThumbnailUrl() }}?' + Date.now()
                        }
                    }"
                />
            @else
                <x-inspirecms-support::media-library.thumbnail-icon 
                    :icon="$mediaItem->getThumbnail()" 
                />
            @endif
        </div>

        <!-- Item Info -->
        <div class="text-center title-ctn">
            <p class="text-sm font-medium truncate">{{ $mediaItem->title }}</p>
            @if ($mediaItem->isFolder())
                <p class="text-xs text-gray-500">
                    {{ __('inspirecms-support::media-library.messages.total_xxx_items', ['count' => $mediaItem->children_count]) }}
                </p>
            @endif
        </div>
    </div>
</div>