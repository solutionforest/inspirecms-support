@php
    $actions = $this->getVisibleMediaItemActions();
    $mediaItemForActions = collect($this->selectedMediaId);
@endphp

<div class="media-library__details">
    @if ($mediaItemForActions->isNotEmpty())
        <div class="header pb-4">
            <div class="heading">
                <span class="title">
                    {{ __('inspirecms-support::media-library.messages.xxx_items_selected', ['count' => $mediaItemForActions->count()]) }}
                </span>
            </div>
            <div class="actions">
                <x-inspirecms-support::media-library.actions
                    :actions="$actions" 
                    :media-item="$mediaItemForActions"
                />
                <x-filament::icon-button
                    icon="heroicon-o-x-mark"
                    title="Deselected all"
                    color="gray"
                    wire:click="$parent.deselectAllMedia"
                />
            </div>
        </div>
    @endif

    @if ($this->canViewInformation($mediaDetailRecord))
        <div class="main">
            <div class="title-ctn">
                <span class="title">{{ $mediaDetailRecord->title }}</span>
            </div>
            <div class="thumbnail-ctn">
                @if($mediaDetailRecord->isImage() || $mediaDetailRecord->isSvg())
                    <img loading="lazy" 
                        x-data="dynamicImage({
                            baseUrl: @js($mediaDetailRecord->getThumbnailUrl()),
                            mediaId: @js($mediaDetailRecord->getKey()),
                            refreshWindowEvents: ['media-thumb-updated'],
                        })"
                        data-base-url="{{ $mediaDetailRecord->getThumbnailUrl() }}"
                        :src="src"
                    />
                @else
                    <x-inspirecms-support::media-library.thumbnail-icon 
                        :icon="$mediaDetailRecord->getThumbnail()"
                        @class(['folder-icon' => $mediaDetailRecord->isFolder()])
                        @style([\Filament\Support\get_color_css_variables('warning', [400, 500, 600]) => $mediaDetailRecord->isFolder()])
                    />
                @endif
            </div>

            <div class="information-ctn">
                <div class="pb-4">
                    <span class="font-bold">{{ __('inspirecms-support::media-library.detail_info.heading') }}</span>
                </div>
                <div class="information-content-ctn">
                    {{ $this->mediaDetailInfolist }}
                </div>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />

</div>