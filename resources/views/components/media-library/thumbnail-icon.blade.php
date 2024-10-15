@props(['icon'])

@if (str_starts_with($icon, 'inspirecms-support::'))
    <x-filament::icon :alias="$icon" {{ $attributes}} />
@else
    <x-filament::icon :icon="$icon" {{ $attributes }} />
@endif
