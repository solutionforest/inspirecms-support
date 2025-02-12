@props([
    'tag' => 'ul',
    'role' => 'list',
    'items' => [], 
    'modelExplorer', 
    'translatable' => false, 
    'translatableLocale' => null, 
    'spaMode' => false,
    'isDisabled' => false,
])

<{{ $tag }} 
    {{ $attributes
        ->merge([
            'class' => 'flex flex-col gap-y-1',
            'role' => $role,
        ]) 
    }}
>
    @foreach ($items as $item)
        <x-inspirecms-support::model-explorer.group
            :item="$item" 
            :model-explorer="$modelExplorer"
            :translatable="$translatable"
            :translatable-locale="$translatableLocale"
            :spa-mode="$spaMode"
            :is-disabled="$isDisabled"
        />
    @endforeach
</{{ $tag }}>