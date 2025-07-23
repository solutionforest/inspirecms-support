@php
    $statePath = $getStatePath();
    $id = $getId();
    $isDisabled = $isDisabled();

    $limitedStateCount = $getLimitDisplay();
    $cachedSelectedAssets = collect($getCachedSelectedAssets());
    $limitedState = $limitedStateCount != null ? $cachedSelectedAssets->take($limitedStateCount) : $cachedSelectedAssets;
    $stateCount = $cachedSelectedAssets->count();

    $height = $width = '3rem';

    $hasLimitedRemainingText = $limitedStateCount != null && $limitedStateCount < $stateCount;

    $imgStyles = "height: $height; width: $width; object-fit: cover;";
    $remainingTextCtnStyles = "padding: 0 4rem;";
    $itemCtnClasses = 'item-content';
    $itemCtnStyles = 'width: 10rem;';

    $filterTypes = $getFilterTypes();
    $modalId = $getMediaLibraryModalId();
    $mediaPickerModalConfig = $getMediaLibraryModalConfig($filterTypes);
@endphp
<div 
    {{
        $attributes
            ->merge($getExtraAttributes(), escape: false)
            ->class(['fi-fo-media-picker'])
    }}
    x-data="{ 
        state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }} 
    }"
    x-on:close-modal.window="
        if ($event.detail.id !== @js($modalId) || $event.detail.statePath != @js($statePath)) {
            return;
        }

        let isSave = $event.detail?.save ?? false;

        if (isSave === true) {
            $wire.dispatchFormEvent('mediaPicker::select', '{{ $statePath }}', $event.detail?.data?.selected)
        }
    "
    x-on:open-modal.window="
        if ($event.detail.id !== @js($modalId) || $event.detail.statePath !== @js($statePath)) {
            return;
        }

        $dispatch('media-picker-setup', { 
            selected: state,
            statePath: @js($statePath),
            config: @js($mediaPickerModalConfig),
        });
    "
>

    <x-dynamic-component
        :component="$getFieldWrapperView()"
        :field="$field"
    >
        <div class="flex gap-x-2 overflow-x-auto">
            @foreach ($limitedState as $asset)
                <!-- Item Content -->
                <div 
                    @class([
                        'flex-none px-3 py-6',
                        $itemCtnClasses,
                    ])
                    @style([$itemCtnStyles])
                    wire:key="{{ $id }}.previewitem.{{ $asset->getKey() }}"
                >
                    <!-- Thumbnail -->
                    <div class="flex flex-col items-center justify-center gap-3">
                        @if ($asset->isImage())
                            <img loading="lazy" 
                                alt="{{ $asset->getKey() }}" 
                                style="{{ $imgStyles }}"
                                :src="src"
                                x-data="dynamicImage({
                                    baseUrl: @js($asset->getThumbnailUrl()),
                                    mediaId: @js($asset->getKey()),
                                    cacheBuster: true,
                                    retryAttempts: 3,
                                    retryDelay: 1000,
                                })"
                            />
                        @else
                            <x-inspirecms-support::media-library.thumbnail-icon 
                                :icon="$asset->getThumbnail()"
                                style="{{ $imgStyles }}"
                                class="text-gray-500 dark:text-gray-400"
                            />
                        @endif
                    </div>
                    <!-- Item Info -->
                    <div class="text-center title-ctn">
                        <p class="text-sm font-medium truncate">{{ $asset->title }}</p>
                    </div>
                </div>
            @endforeach
            @if ($hasLimitedRemainingText)
                <div
                    style="{{ $remainingTextCtnStyles }}"
                    @class([
                        'flex flex-col items-center justify-center',
                        $itemCtnClasses,
                    ])
                >
                    <span class="text-xs">
                        +{{ $stateCount - $limitedStateCount }}
                    </span>
                </div>
            @endif
        </div>

        <div class="flex gap-2">
            @if (! $isDisabled)
                <x-filament::button color="gray" x-on:click="$wire.dispatchFormEvent('mediaPicker::clearSelected', '{{ $statePath }}')">
                    {{ __('inspirecms-support::media-library.buttons.clear.label') }}
                </x-filament::button>
                <x-filament::button x-on:click="$dispatch('open-modal', { id: '{{ $modalId }}', statePath: '{{ $statePath }}' })">
                    {{ __('inspirecms-support::media-library.buttons.select.label') }}
                </x-filament::button>
            @endif
        </div>

    </x-dynamic-component>
</div>