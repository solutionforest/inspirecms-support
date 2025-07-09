@php
    $actions = $this->getCachedMediaItemActions();
    $mediaItemForActions = collect($this->selectedMediaId);
@endphp

<div class="media-library__details">
    @if ($mediaItemForActions->isNotEmpty())
        <div class="header">
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

    @if ($this->canViewInformation($toggleMedia))
        <div class="main">
            <div class="title-ctn">
                <span class="title">{{ $toggleMedia->title }}</span>
            </div>
            <div class="thumbnail-ctn">
                @if($toggleMedia->isImage() || $toggleMedia->isSvg())
                    <img loading="lazy" 
                        x-data="{ src: '{{ $toggleMedia->getThumbnailUrl() }}?' + Date.now() }"
                        :src="src"
                        x-on:media-thumb-updated.window="(event) => {
                            const updatedId = (Array.isArray(event.detail) ? event.detail[0]?.id : event.detail?.id) || null;
                            if (!updatedId) {
                                return;
                            }
                            if (updatedId === '{{ $toggleMedia->getKey() }}') {
                                src = '{{ $toggleMedia->getThumbnailUrl() }}?' + Date.now()
                            }
                        }"
                    />
                @else
                    <x-inspirecms-support::media-library.thumbnail-icon 
                        :icon="$toggleMedia->getThumbnail()"
                    />
                @endif
            </div>

            <div class="information-ctn">
                <div class="pb-4">
                    <span class="font-bold">{{ __('inspirecms-support::media-library.detail_info.heading') }}</span>
                </div>
                <div class="information-content-ctn">
                    @foreach ($this->getInformationFor($toggleMedia) ?? [] as $item)
                        <div class="information-content__row">
                            <span class="information-content__row__label">{{ $item['label'] ?? null }}</span>
                            <span class="information-content__row__value">{{ $item['value'] ?? null }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />

</div>