@php
    $actions = $this->getCachedMediaItemActions();
    $mediaItemForActions = collect($this->selectedMediaId);
@endphp

<div class="media-library__details">
    <div class="header">
        <div class="heading">
            <span class="title">{{ $this->getTitleFor($selectedMedia) }}</span>
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

    @if ($selectedMedia != null)
        <div class="main">
            <div class="thumbnail-ctn">
                @if($selectedMedia->isImage())
                    <img loading="lazy" src="{{ $selectedMedia->getThumbnailUrl() }}" />
                @else
                    <x-inspirecms-support::media-library.thumbnail-icon 
                        :icon="$selectedMedia->getThumbnail()"
                    />
                @endif
            </div>

            <div class="information-ctn">
                <div class="pb-4">
                    <span class="font-bold">Information</span>
                </div>
                @foreach ($this->getInformationFor($selectedMedia) ?? [] as $item)
                    <div class="grid grid-cols-3 gap-2 pb-2">
                        <span class="text-sm text-gray-500">{{ $item['label'] ?? null }}</span>
                        <span class="col-span-2 font-semibold truncate">{{ $item['value'] ?? null }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <x-filament-actions::modals />

</div>