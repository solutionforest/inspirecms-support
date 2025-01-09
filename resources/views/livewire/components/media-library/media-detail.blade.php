@php
    $actions = $this->getCachedMediaItemActions();
    $mediaItemForActions = collect($this->selectedMediaId);
@endphp

<div class="media-library__details">
    @if ($mediaItemForActions->isNotEmpty())
        <div class="header">
            <div class="heading">
                <span class="title">{{ $mediaItemForActions->count() }} items selected</span>
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
                    wire:click="$parent.resetSelectedMedia"
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
                @if($toggleMedia->isImage())
                    <img loading="lazy" src="{{ $toggleMedia->getThumbnailUrl() }}" />
                @else
                    <x-inspirecms-support::media-library.thumbnail-icon 
                        :icon="$toggleMedia->getThumbnail()"
                    />
                @endif
            </div>

            <div class="information-ctn">
                <div class="pb-4">
                    <span class="font-bold">Information</span>
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