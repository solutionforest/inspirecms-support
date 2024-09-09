@php
    $rootItems = $getRootItems();
    $selectedFilePath = $getSelectedFilePath();
@endphp
<x-inspirecms-support::tree-node class="file-explorer">
    
    @if (empty($rootItems))
        <p>@lang('inspirecms-support::tree-node.no_files_or_directories')</p>
    @else
        @foreach ($rootItems as $item)
            <livewire:file-explorer-item :item="$item" :key="$item->path" />
        @endforeach
    @endif

    <x-slot:mainViewContent>
        @if ($selectedFilePath && !$isSelectedItemDirectory())
            <form wire:submit="saveSelectedItem">

                <div class="pb-4">
                    {{ $this->selectedFileItemForm }}
                </div>
                
                <x-filament-panels::form.actions
                    :actions="$this->getSelectedFileItemFormActions()"
                />
            </form>
            
            <x-filament-actions::modals />

        @elseif ($selectedFilePath && $isSelectedItemDirectory())
            <p class="text-gray-500 non-selectable-text">
                @lang('inspirecms-support::tree-node.selected_item_is_directory')
            </p>
        @else
            <p class="text-gray-500 non-selectable-text">
                @lang('inspirecms-support::tree-node.select_file_to_view')
            </p>
        @endif
    </x-slot:mainViewContent>
</x-inspirecms-support::tree-node>