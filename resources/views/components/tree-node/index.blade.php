@props(['mainViewContent' => null])
<div 
    {{ $attributes->merge([
        'class' => 'tree-node-layout flex min-h-screen',
    ]) }}
    x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('tree-node', 'solution-forest/inspirecms-support'))]"
    x-data="{}"
    x-load-js="[@js(\Filament\Support\Facades\FilamentAsset::getScriptSrc('tree-node', 'solution-forest/inspirecms-support'))]"
>
    <x-inspirecms-support::tree-node.side-bar>
        {{ $slot }}
    </x-inspirecms-support::tree-node.side-bar>

    <div 
        class="tree-node-resizer cursor-col-resize w-1 bg-gray-200 hover:bg-gray-300 active:bg-gray-400"
    ></div>
    
    <div class="tree-node-main flex-grow overflow-auto p-4">
        @isset($mainViewContent)
            {{ $mainViewContent }}
        @endisset
    </div>
</div>