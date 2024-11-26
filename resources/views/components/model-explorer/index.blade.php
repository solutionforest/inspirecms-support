@props(['modelExplorer', 'items' => [], 'expandedItemsStateKey' => null, 'translatable' => false, 'translatableLocale' => null])
@php
    $selectedKey = $this->selectedModelItemKey;
@endphp
<x-inspirecms-support::tree-node class="model-explorer">
    <x-slot:sidebar>
        <div x-data="{
            expandedItems: @if (filled($expandedItemsStateKey)) $wire.entangle('{{ $expandedItemsStateKey }}') @else [] @endif,
            async toggleItem(key, currDepth) {
                if (this.expandedItems.includes(key)) {
                    this.expandedItems = this.expandedItems.filter(item => item !== key);
                } else {
                    this.expandedItems.push(key);
                }
    
                await this.fetchNodes(key, currDepth + 1);
            },
            selectItem(key) {            
                this.isExpandedSidebar = false
                Livewire.dispatch('selectItem', [key])
            },
            isExpanded(key) {
                return this.expandedItems.includes(key);
            },
            async fetchNodes(key, depth) {
                Livewire.dispatch('getNodes', [key, depth])
                while (this.fetching) {
                    await new Promise(resolve => setTimeout(resolve, 100));
                }
            },
            init() {
                //
            }
        }">
            <ul class="flex flex-col gap-y-1">
                @foreach ($items as $item)
                    <x-inspirecms-support::model-explorer.item  
                        :item="$item" 
                        :selectedKey="$selectedKey"
                        :model-explorer="$modelExplorer"
                        :translatable="$translatable"
                        :translatable-locale="$translatableLocale"
                    />
                @endforeach
            </ul>
        </div>
    </x-slot:sidebar>
    <x-slot:main>
        {{ $slot }}
    </x-slot:main>
</x-inspirecms-support::tree-node>