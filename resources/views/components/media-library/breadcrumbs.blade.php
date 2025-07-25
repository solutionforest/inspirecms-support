@props([
    'breadcrumbs' => [],
])

@php
    $iconClasses = 'fi-breadcrumbs-item-separator flex h-5 w-5 text-gray-400 dark:text-gray-500';
    $itemLabelClasses = 'fi-breadcrumbs-item-label text-sm font-medium text-gray-500 dark:text-gray-400';
@endphp

<nav {{ $attributes->class(['fi-breadcrumbs']) }}>
    <ol class="fi-breadcrumbs-list flex flex-wrap items-center gap-x-2">
        @foreach ($breadcrumbs as $key => $label)
            <li class="fi-breadcrumbs-item flex items-center gap-x-2">
                @if (! $loop->first)
                    <x-filament::icon
                        alias="breadcrumbs.separator"
                        icon="heroicon-m-chevron-right"
                        @class([
                            $iconClasses,
                            'rtl:hidden',
                        ])
                    />

                    <x-filament::icon
                        {{-- @deprecated Use `breadcrubs.separator.rtl` instead of `breadcrumbs.separator` for RTL. --}}
                        :alias="['breadcrumbs.separator.rtl', 'breadcrumbs.separator']"
                        icon="heroicon-m-chevron-left"
                        @class([
                            $iconClasses,
                            'ltr:hidden',
                        ])
                    />
                @endif

                <span
                    wire:click="openFolder('{{ $key }}')"
                    class="{{ $itemLabelClasses }} cursor-pointer transition duration-75 hover:text-gray-700 dark:hover:text-gray-200"
                >
                    {{ $label }}
                </span>
            </li>
        @endforeach
    </ol>
</nav>