@props(['mainViewContent' => null])
<div 
    {{ $attributes->merge([
        'class' => 'tree-node-layout min-h-screen md:flex',
    ]) }}
    x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('tree-node', 'solution-forest/inspirecms-support'))]"
>
    <div 
        x-data="{ isExpandedSidebar: false }"
        class="lg:hidden"
    >
        <x-filament::icon-button
            color="gray"
            icon="heroicon-o-bars-3"
            icon-size="lg"
            x-cloak
            @click="isExpandedSidebar = !isExpandedSidebar"
            class="p-2"
        />
        <div x-cloak x-show="isExpandedSidebar" 
            @click.outside="isExpandedSidebar = false"
            class="absolute flex z-20 md:z-0 w-[--sidebar-width] bg-white dark:bg-gray-900 rounded-lg mt-2">
            <div class="w-full transition-all duration-300 py-5">
                {{ $slot }}
            </div>
        </div>
    </div>

    <x-inspirecms-support::tree-node.side-bar class="hidden lg:block">
        {{ $slot }}
    </x-inspirecms-support::tree-node.side-bar>

    <div 
        class="tree-node-resizer hidden lg:block bg-gray-200 hover:bg-gray-300 active:bg-gray-400"
    ></div>
    
    <div class="tree-node-main flex-grow overflow-auto p-4">
        @isset($mainViewContent)
            {{ $mainViewContent }}
        @endisset
    </div>
</div>