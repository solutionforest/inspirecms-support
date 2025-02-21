@php
    $statePath = $getStatePath();
    $id = $getId();
    $isDisabled = $isDisabled();

    $limitedStateCount = $getLimitDisplay();
    $cachedSelectedAssets = collect($getCachedSelectedAssets());
    $limitedState = $cachedSelectedAssets->take($limitedStateCount);
    $stateCount = $cachedSelectedAssets->count();

    $height = $width = '8rem';

    $hasLimitedRemainingText = $limitedStateCount < $stateCount;

    $ringClasses = 'ring-white dark:ring-gray-900 ring-2';
    $imgStyles = "height: $height; width: $width;";
    $imgPlaceholderClasses = 'flex items-center justify-center bg-gray-100 font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400 rounded-lg';

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
        <div class="flex flex-wrap gap-1.5">
            @foreach ($limitedState as $asset)
                <div 
                    @class([
                        'thumbnail-ctn',
                        $imgPlaceholderClasses,
                        $ringClasses,
                    ])
                    style="{{ $imgStyles }}"
                >
                    @if ($asset->isImage())
                        <img loading="lazy" 
                            src="{{ $asset->getThumbnailUrl() }}" 
                            alt="{{ $asset->getKey() }}" 
                            class="max-w-none w-full object-cover object-center"
                        />
                    @else
                        <div class="flex flex-col items-center justify-center text-xs text-gray-500 dark:text-gray-400 w-full">
                            <x-inspirecms-support::media-library.thumbnail-icon 
                                :icon="$asset->getThumbnail()"
                                class="size-16" 
                            />
                            <span class="max-w-full truncate select-none">{{ $asset->title }}</span>
                        </div>

                    @endif
                </div>
            @endforeach
            @if ($hasLimitedRemainingText)
                <div
                    style="{{ $imgStyles }}"
                    @class([
                        $imgPlaceholderClasses,
                        'text-xs',
                        $ringClasses,
                    ])
                >
                    <span class="-ms-0.5">
                        +{{ $stateCount - $limitedStateCount }}
                    </span>
                </div>
            @endif
        </div>

        <div class="flex gap-2">
            @if (! $isDisabled)
                <x-filament::button color="gray" x-on:click="$wire.dispatchFormEvent('mediaPicker::clearSelected', '{{ $statePath }}')">
                    {{ __('inspirecms-support::actions.clear.label') }}
                </x-filament::button>
                <x-filament::button x-on:click="$dispatch('open-modal', { id: '{{ $modalId }}', statePath: '{{ $statePath }}' })">
                    {{ __('inspirecms-support::actions.select.label') }}
                </x-filament::button>
            @endif
        </div>

    </x-dynamic-component>
</div>