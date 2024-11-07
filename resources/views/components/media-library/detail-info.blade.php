@props(['mediaItem', 'mediaActions' => null])
<div class="media-library__item_detail">
    @php
        $selectedMediaUrl = $mediaItem->getUrl();
    @endphp
    <div class="media-library__item_detail__content p-4">
        @if (!$mediaItem->isFolder())
            <div class="w-full flex justify-center">
                <a class="media-library__item_detail__thumb" href="{{ $selectedMediaUrl }}" target="_blank">
                    @if ($mediaItem->isImage())
                        <img src="{{ $mediaItem->getUrl() }}" alt="{{ $mediaItem->title }}" />
                    @else
                        <x-inspirecms-support::media-library.thumbnail-icon :icon="$mediaItem->getThumbnail()"
                            class="media-library__item_detail__content__icon" />
                    @endif
                </a>
            </div>
        @else
            <div class="media-library__item_detail__thumb">
                <x-inspirecms-support::media-library.thumbnail-icon :icon="$mediaItem->getThumbnail()"
                    class="media-library__item_detail__content__icon" />
            </div>
        @endif
        <div class="media-library__item_detail__content__details">
            <div class="media-library__item_detail__content__details__meta">
                @php
                    $media = $mediaItem->isFolder() ? null : $mediaItem->getFirstMedia();
                    $columns = $mediaItem->getDisplayedColumns();

                    $mediaData = collect($columns)
                        ->map(function ($key) use ($media, $mediaItem) {
                            $fallback = match ($key) {
                                'created_at', 'updated_at' => trans(
                                    "inspirecms-support::media-library.detail_info.{$key}.empty",
                                ),
                                default => '',
                            };
                            $customPropertyKey = str_replace('custom-property.', '', $key);
                            $value = match ($key) {
                                'size' => ($mediaItem->isFolder() ? '' : $media?->human_readable_size) ?? $fallback,
                                'created_at', 'updated_at' => ($mediaItem->isFolder()
                                    ? $mediaItem?->{$key}->format('Y-m-d H:i:s')
                                    : $media?->{$key}->format('Y-m-d H:i:s')) ?? $fallback,
                                'uploaded_by', 'created_by' => $mediaItem->author?->name ?? $fallback,
                                // Default for not custom properties
                                $customPropertyKey => ($mediaItem->isFolder()
                                    ? $mediaItem?->{$key}
                                    : $media?->{$key}) ?? $fallback,
                                // Default for custom properties
                                default => $media->getCustomProperty($customPropertyKey) ?? $fallback,
                            };
                            return [
                                'label' => trans("inspirecms-support::media-library.detail_info.{$key}.label"),
                                'value' => $value,
                            ];
                        })
                        ->all();
                @endphp
                <x-filament::grid default="3">
                    @foreach ($mediaData as $key => $arr)
                        <x-filament::grid.column default="1">
                            <span>{{ $arr['label'] }}</span>
                        </x-filament::grid.column>
                        <x-filament::grid.column default="2" class="">
                            <strong>{{ $arr['value'] }}</strong>
                        </x-filament::grid.column>
                    @endforeach
                </x-filament::grid>
            </div>
        </div>
    </div>
    <div class="media-library__item_detail__content__actions">

        @if (!$mediaItem->isFolder() && $mediaActions)
            {{ $mediaActions }}
        @endif

        @if ($mediaItem->isFolder())
            <x-filament::button wire:click="openFolder" color="gray" icon="heroicon-o-folder">
                {{ trans('inspirecms-support::media-library.actions.open_folder.label') }}
            </x-filament::button>
        @endif

        <x-filament::button wire:click="deleteMedia" color="danger" icon="heroicon-o-trash">
            {{ trans('inspirecms-support::media-library.actions.delete.label') }}
        </x-filament::button>
    </div>
</div>
