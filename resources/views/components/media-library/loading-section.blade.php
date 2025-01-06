@php
    if ((! isset($count)) || (! is_numeric($count))) {
        $count = 1;
    }
    $columns ??= [];
    $columnSpan ??= [];
    $columnStart ??= [];
@endphp

<x-filament::grid
    :default="$columns['default'] ?? 1"
    :sm="$columns['sm'] ?? null"
    :md="$columns['md'] ?? null"
    :lg="$columns['lg'] ?? null"
    :xl="$columns['xl'] ?? null"
    :twoXl="$columns['2xl'] ?? null"
    class="media-library__loading-sections gap-4"
>
    @for ($i = 0; $i < $count; $i++)
        <x-filament::grid.column default="1" class="media-library__loading-section" 
            @style([
                "height: $height" => isset($height),
            ])
        >
            <div class="bar-ctn">
                <div class="bar"></div>
                <div class="bar"></div>
            </div>
        </x-filament::grid.column>
    @endfor
</x-filament::grid>