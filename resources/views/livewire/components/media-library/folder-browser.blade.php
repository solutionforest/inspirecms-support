<div class="media-library__folders">
    <div class="header">
        <div class="heading">
            <span class="title">{{ __('inspirecms-support::media-library.folder.plural') }}</span>
        </div>
    </div>
    <div class="main">
        @foreach ($folders ?? [] as $mediaItem)
            @php
                $isSeleted = $mediaItem->getKey() == $this->parentRecord?->getKey();
                $childrenCount = $mediaItem->children_count ?? 0;

                $actions = $this->getCachedMediaItemActions();
            @endphp
            <div 
                @class([
                    'folder-item',
                    'selected' => $isSeleted,
                ])
            >
                <x-inspirecms-support::media-library.thumbnail-icon 
                    :icon="$mediaItem->getThumbnail()" 
                    class="icon"
                    @style([
                        \Filament\Support\get_color_css_variables('warning', [400, 500])
                    ])
                />

                <div
                    class="main"
                    wire:click="$parent.openFolder('{{ $mediaItem->getKey() }}')"
                >
                    <span class="title">
                        {{ $mediaItem->title }}
                    </span>
                    <span class="description">
                        {{ __('inspirecms-support::media-library.messages.total_xxx_items', ['count' => $childrenCount]) }}
                    </span>
                </div>

                <div class="actions">
                    <x-inspirecms-support::media-library.actions
                        :media-item="$mediaItem"
                        :actions="$actions"
                    />
                </div>
            </div>
        @endforeach
    </div>

    <x-filament-actions::modals />
</div>