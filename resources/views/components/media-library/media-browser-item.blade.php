@props(['mediaItem'])
<div 
    {{ $attributes->class([
        'media-library__content__items__item',
        'non-image-item' => ! $mediaItem->isImage(),
    ]) }}
    x-bind:class="{ 'selected': isMediaSelected('{{ $mediaItem->getKey() }}') }"
    @style([
        \Filament\Support\get_color_css_variables('primary', [200, 300, 400, 500]),
    ])
>
    <div class="media-library__content__items__item__thumb"
        @click="selectMedia('{{ $mediaItem->getKey() }}', @js($mediaItem->isFolder()))"
        @if ($mediaItem->isFolder())
            @dblclick="$wire.openFolder('{{ $mediaItem->getKey() }}')"
        @endif
    >
        @if ($mediaItem->isImage())
            <img src="{{ $mediaItem->getThumbnailUrl() }}" alt="{{ $mediaItem->title }}" />
        @else
            <x-inspirecms-support::media-library.thumbnail-icon :icon="$mediaItem->getThumbnail()" class="media-library__content__items__item__thumb__icon" />
        @endif
    </div>
    <span class="media-library__content__items__item__thumb__title">
        {{ $mediaItem->title }}
    </span>
</div>