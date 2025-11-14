@php
    $statePath = $getStatePath();
    $id = $getId();
    $key = $getKey();
    $isDisabled = $isDisabled();
    
    $cachedSelectedAssets = collect($getCachedSelectedAssets());
    $limitedStateCount = $getLimitDisplay();
    $limitedState = $limitedStateCount != null ? $cachedSelectedAssets->take($limitedStateCount) : $cachedSelectedAssets;
    $stateCount = $cachedSelectedAssets->count();
    $hasLimitedRemainingText = $limitedStateCount != null && $limitedStateCount < $stateCount;

    $height = $width = '3rem';

    $hasLimitedRemainingText = $limitedStateCount != null && $limitedStateCount < $stateCount;

    $imgStyles = "height: $height; width: $width; object-fit: cover;";
    $remainingTextCtnStyles = "padding: 0 4rem;";
    $itemCtnClasses = 'item-content';
    $itemCtnStyles = 'width: 10rem;';
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        {{
            $attributes
                ->merge([
                    'id' => $id,
                ], escape: false)
                ->merge($getExtraAttributes(), escape: false)
                ->class([
                    'fi-fo-media-picker',
                    'fi-fo-media-picker-disabled' => $isDisabled,
                    // 'fi-fo-media-picker-multiple' => $isMultiple,
                ])
        }}
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
                                data-base-url="{{ $asset->getThumbnailUrl() }}"
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
                {{ $getAction('clear') }}
                {{ $getAction('select') }}
            @endif
        </div>
    </div>
    
</x-dynamic-component>