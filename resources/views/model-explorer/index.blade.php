@php
    $items = $this->getGroupedNodeItems();
@endphp

<x-inspirecms-support::tree-node class="model-explorer">
    <div x-data="{
        expandedItems: [],
        fetching: false,
        async toggleItem(key, currDepth) {
            if (this.expandedItems.includes(key)) {
                this.expandedItems = this.expandedItems.filter(item => item !== key);
            } else {
                this.expandedItems.push(key);
            }

            await this.fetchNodes(key, currDepth + 1);
        },
        isExpanded(key) {
            return this.expandedItems.includes(key);
        },
        async fetchNodes(key, depth) {
            this.fetching = true;
            Livewire.dispatch('getNodes', [key, depth])
            Livewire.dispatch('selectItem', [key])
            while (this.fetching) {
                await new Promise(resolve => setTimeout(resolve, 100));
            }
        },
        init() {
            //
        }
    }">
        <ul>
            @foreach ($items as $item)
                <x-inspirecms-support::model-explorer.item  
                    :item="$item" 
                />
            @endforeach
        </ul>
    </div>

    <x-slot:mainViewContent>
        @if (filled($this->selectedModelItemKey))
            <form wire:submit="saveSelectedItem">
                <div class="pb-4">
                    {{ $this->selectedModelItemForm }}
                </div>
                <x-filament-panels::form.actions
                    :actions="$this->getSelectedModelItemFormActions()"
                />
            </form>
            <x-filament-actions::modals />
            
        @endif
    </x-slot:mainViewContent>
</x-inspirecms-support::tree-node>