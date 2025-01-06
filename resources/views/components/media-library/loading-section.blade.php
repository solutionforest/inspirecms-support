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
    class="media-library__loading-section gap-4"
>
    @for ($i = 0; $i < $count; $i++)
        <x-filament::grid.column default="1" class="p-4 rounded-lg ring-1 ring-gray-300/50 dark:ring-white/10" 
            @style([
                "height: $height" => isset($height),
            ])
        >
            <div class="animate-pulse grid grid-cols-1 gap-4">
                <div class="h-2 rounded bg-gray-300 dark:bg-white/10"></div>
                <div class="h-2 rounded bg-gray-300 dark:bg-white/10"></div>
            </div>
        </x-filament::grid.column>
    @endfor
</x-filament::grid>