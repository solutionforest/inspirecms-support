@props(['mediaItem'])
<div @class([
    'media-library__item_detail', 
    'flex flex-col gap-2',
    'border border-gray-200 dark:border-gray-400 rounded-md bg-gray-100 dark:bg-gray-800',
])>
    @php
        $selectedMediaUrl = $mediaItem->getUrl();
    @endphp
    <div class="media-library__item_detail__content flex flex-1 flex-col gap-3 p-4">
        <div class="media-library__item_detail__thumb flex flex-col justify-center items-center">
            @if ($mediaItem->isImage())
                <img src="{{ $mediaItem->getUrl() }}" alt="{{ $mediaItem->title }}" class="object-cover rounded-md w-64 h-64" />
            @else
                <x-icon :name="$mediaItem->getThumbnail()" class="media-library__item_detail__content__icon w-8 h-8" />
            @endif
        </div>
        <div class="media-library__item_detail__content__details">
            <span class="media-library__item_detail__content__details__title">
                {{ $mediaItem->title }}
            </span>
            <div class="media-library__item_detail__content__details__meta">
                @php
                    $selectedMediaItem = $mediaItem->getFirstMedia();
                @endphp
                @if ($selectedMediaItem)
                    <x-filament::grid default="3" class="text-sm text-thin">
                        <x-filament::grid.column default="1">
                            <span>Mime type</span>
                        </x-filament::grid.column>
                        <x-filament::grid.column default="2">
                            <span>{{ $selectedMediaItem->mime_type }}</span>
                        </x-filament::grid.column>

                        <x-filament::grid.column default="1">
                            <span>Size</span>
                        </x-filament::grid.column>
                        <x-filament::grid.column default="2">
                            <span>{{ $selectedMediaItem->size }}</span>
                        </x-filament::grid.column>

                        <x-filament::grid.column default="1">
                            <span>Disk</span>
                        </x-filament::grid.column>
                        <x-filament::grid.column default="2">
                            <span>{{ $selectedMediaItem->disk }}</span>
                        </x-filament::grid.column>
                        
                        <x-filament::grid.column default="1">
                            <span>Create at</span>
                        </x-filament::grid.column>
                        <x-filament::grid.column default="2">
                            <span>{{ $selectedMediaItem->created_at }}</span>
                        </x-filament::grid.column>
                        
                        <x-filament::grid.column default="1">
                            <span>Update at</span>
                        </x-filament::grid.column>
                        <x-filament::grid.column default="2">
                            <span>{{ $selectedMediaItem->updated_at }}</span>
                        </x-filament::grid.column>
                    </x-filament::grid>
                @endif
            </div>
        </div>
    </div>
    <div class="media-library__item_detail__content__actions flex gap-2 justify-center pb-4">
        @if (filled($selectedMediaUrl))
            <x-filament::button
                tag="a"
                target="_blank"
                href="{{ $selectedMediaUrl }}"
                color="gray"
            >
                View
            </x-filament::button>
        @endif
        @if ($mediaItem->isFolder())
            <x-filament::button
                wire:click="openFolder"
                color="gray"
            >
                Open Folder
            </x-filament::button>
        @endif
        <x-filament::button
            wire:click="deleteMedia"
            color="danger"
        >
            Delete
        </x-filament::button>
    </div>
</div>