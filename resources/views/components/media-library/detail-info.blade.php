@props(['mediaItem'])
<div class="media-library__item_detail">
    @php
        $selectedMediaUrl = $mediaItem->getUrl();
    @endphp
    <div class="media-library__item_detail__content p-4">
        <div class="media-library__item_detail__thumb">
            @if ($mediaItem->isImage())
                <img src="{{ $mediaItem->getUrl() }}" alt="{{ $mediaItem->title }}" />
            @else
                <x-inspirecms-support::media-library.thumbnail-icon :icon="$mediaItem->getThumbnail()" class="media-library__item_detail__content__icon" />
            @endif
        </div>
        <div class="media-library__item_detail__content__details">
            <span class="media-library__item_detail__content__details__title">
                {{ $mediaItem->title }}
            </span>
            <div class="media-library__item_detail__content__details__meta">
                @php
                    $selectedMediaItem = $mediaItem->getFirstMedia();
                    $columns = [
                        'mime_type' => [
                            'label' => trans('inspirecms-support::media-library.detail_info.mime_type.label'),
                            'fallback' => '',
                        ],
                        'size' => [
                            'label' => trans('inspirecms-support::media-library.detail_info.size.label'),
                            'fallback' => '',
                        ],
                        'disk' => [
                            'label' => trans('inspirecms-support::media-library.detail_info.disk.label'),
                            'fallback' => '',
                        ],
                        'created_at' => [
                            'label' => trans('inspirecms-support::media-library.detail_info.created_at.label'),
                            'fallback' => trans('inspirecms-support::media-library.detail_info.created_at.empty'),
                        ],
                        'updated_at' => [
                            'label' => trans('inspirecms-support::media-library.detail_info.updated_at.label'),
                            'fallback' => trans('inspirecms-support::media-library.detail_info.updated_at.empty'),
                        ],
                    ];
                @endphp
                @if ($selectedMediaItem)
                    <x-filament::grid default="3">
                        @foreach ($columns as $key => $arr)
                            <x-filament::grid.column default="1">
                                <span>{{ $arr['label'] }}</span>
                            </x-filament::grid.column>
                            <x-filament::grid.column default="2">
                                <span>{{ $selectedMediaItem?->{$key} ?? $arr['fallback'] }}</span>
                            </x-filament::grid.column>
                        @endforeach
                    </x-filament::grid>
                @endif
            </div>
        </div>
    </div>
    <div class="media-library__item_detail__content__actions">
        @if (filled($selectedMediaUrl))
            <x-filament::button
                tag="a"
                target="_blank"
                href="{{ $selectedMediaUrl }}"
                color="gray"
            >
                {{ trans('inspirecms-support::media-library.actions.view.label') }}
            </x-filament::button>
        @endif
        @if ($mediaItem->isFolder())
            <x-filament::button
                wire:click="openFolder"
                color="gray"
            >
                {{ trans('inspirecms-support::media-library.actions.open_folder.label') }}
            </x-filament::button>
        @endif
        <x-filament::button
            wire:click="deleteMedia"
            color="danger"
        >
            {{ trans('inspirecms-support::media-library.actions.delete.label') }}
        </x-filament::button>
    </div>
</div>