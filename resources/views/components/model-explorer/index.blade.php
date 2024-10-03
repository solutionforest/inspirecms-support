@props(['items' => [], 'expandedItemsStateKey' => null, 'actions' => []])
@php
    $selectedKey = $this->selectedModelItemKey;
@endphp
<x-inspirecms-support::tree-node class="model-explorer">
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
            console.log('Model Explorer Initialized', this.expandedItems);
        }
    }">
        <ul>
            @foreach ($items as $item)
                <x-inspirecms-support::model-explorer.item  
                    :item="$item" 
                    :selectedKey="$selectedKey"
                    :actions="$actions"
                />
            @endforeach
        </ul>
    </div>

    <x-slot:mainViewContent>
        {{ $slot }}
    </x-slot:mainViewContent>
</x-inspirecms-support::tree-node>