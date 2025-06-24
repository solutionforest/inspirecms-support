@props([
    'modelExplorer', 
    'items' => [], 
    'translatable' => false, 
    'translatableLocale' => null,
    'spaMode' => false,
    'isDisabled' => false,
    'skipAlpine' => false,
])

<nav 
    role="tree" 
    aria-orientation="vertical" 
    @unless ($skipAlpine)
        x-data="TreeNode({
            selected: $wire.entangle('selectedModelItemKeys').live,
            expanded: $wire.entangle('expandedModelItemKeys').live,
        })"
    @endunless
    {{ 
        $attributes->merge([
            'class' => 'model-explorer',
        ])
    }}
>
    <x-inspirecms-support::tree-node.model-explorer.groups
        :items="$items" 
        :model-explorer="$modelExplorer"
        :translatable="$translatable"
        :translatable-locale="$translatableLocale"
        :spa-mode="$spaMode"
        :is-disabled="$isDisabled"
    />
</nav>
