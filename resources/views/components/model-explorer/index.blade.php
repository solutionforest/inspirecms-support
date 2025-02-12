@props([
    'modelExplorer', 
    'items' => [], 
    'translatable' => false, 
    'translatableLocale' => null,
    'spaMode' => false,
    'isDisabled' => false,
])

<nav 
    role="tree" 
    aria-orientation="vertical" 
    ax-load
    ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('tree-node-component', 'solution-forest/inspirecms-support') }}"
    x-data="treeNode({
        selected: $wire.entangle('selectedModelItemKeys').live,
        expanded: $wire.entangle('expandedModelItemKeys').live,
    })"
    {{ 
        $attributes->merge([
            'class' => 'model-explorer',
        ])
    }}
>
    <x-inspirecms-support::model-explorer.groups
        :items="$items" 
        :model-explorer="$modelExplorer"
        :translatable="$translatable"
        :translatable-locale="$translatableLocale"
        :spa-mode="$spaMode"
        :is-disabled="$isDisabled"
    />
</nav>
