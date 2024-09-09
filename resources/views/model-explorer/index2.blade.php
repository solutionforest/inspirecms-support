@php
    $rootItems = $getRootItems();
    $selectedItem = $getSelectedItem();
@endphp
<x-inspirecms-support::tree-node class="model-explorer">
    
    @if (empty($rootItems))
        <p>empty root message</p>
    @else
        @foreach ($rootItems as $item)
            <livewire:model-explorer-item :item="$item" :key="$item->getKey()" />
        @endforeach
    @endif

    <x-slot:mainViewContent>
        @if ($selectedItem)
            <form wire:submit="saveSelectedModelItem">

                <div class="pb-4">
                    {{ $this->selectedModelItemForm }}
                </div>
                
                <div>
                    @foreach ($this->getSelectedModelItemFormActions() as $action)
                    {{ $action }}
                    @endforeach
                </div>
            </form>
            
            <x-filament-actions::modals />

        @else
            <p class="text-gray-500 non-selectable-text">@lang('inspirecms-support::tree-node.select_model_to_view')</p>
        @endif
    </x-slot:mainViewContent>

</x-inspirecms-support::tree-node>