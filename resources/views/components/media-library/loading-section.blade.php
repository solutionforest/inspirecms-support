@php
    if ((! isset($count)) || (! is_numeric($count))) {
        $count = 1;
    }
    $columns ??= [];
    $columnSpan ??= [];
    $columnStart ??= [];
@endphp

<div
    {{
        (new \Illuminate\View\ComponentAttributeBag)
            ->grid($columns)
            ->class(['media-library__loading-sections gap-4'])
    }}
>
    @for ($i = 0; $i < $count; $i++)
        <div
            {{
                (new \Illuminate\View\ComponentAttributeBag)
                    ->gridColumn($columnSpan, $columnStart)
                    ->class(['media-library__loading-section'])
                    ->style(['height: ' . ($height ?? '8rem')])
            }}
        >
            <div class="bar-ctn">
                <div class="bar"></div>
                <div class="bar"></div>
            </div>
        </div>
    @endfor
</div>