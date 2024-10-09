@props(['mainViewContent' => null])
<div 
    {{ $attributes->merge([
        'class' => 'tree-node-layout',
    ]) }}
    x-data="{ isExpandedSidebar: false }"
>
    <div class="tree-node-sidebar-container">
        <x-filament::icon-button
            color="gray"
            icon="heroicon-o-bars-3"
            icon-size="md"
            x-cloak
            @click="isExpandedSidebar = !isExpandedSidebar"
            class="p-2"
        />
        <div class="tree-node-sidebar"
            x-show="isExpandedSidebar"
            x-cloak
        >
            {{ $slot }}
        </div>
    </div>
    
    <div class="tree-node-main">
        @isset($mainViewContent)
            {{ $mainViewContent }}
        @endisset
    </div>
</div>