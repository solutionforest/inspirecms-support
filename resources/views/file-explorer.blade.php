@php
    $items = $this->getGroupedNodeItems();
    $selectedItemPath = $this->fileExplorerSelectedPath;
@endphp
<x-inspirecms-support::tree-node class="file-explorer">
    
    @if (empty($items))
        <p>@lang('inspirecms-support::tree-node.no_files_or_directories')</p>
    @else
        <div x-data="{
            expandedItems: [],
            fetching: false,
            async toggleItem(key, currDepth) {
                if (this.expandedItems.includes(key)) {
                    this.expandedItems = this.expandedItems.filter(item => item !== key);
                } else {
                    this.expandedItems.push(key);
                }

                this.isExpandedSidebar = false
                await this.fetchNodes(key, currDepth + 1);
            },
            isExpanded(key) {
                return this.expandedItems.includes(key);
            },
            async fetchNodes(key, level) {
                this.fetching = true;
                Livewire.dispatch('getFilesFrom', [key, level])
                Livewire.dispatch('selectFile', [key])
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
                    <x-inspirecms-support::file-explorer.item  
                        :item="$item" 
                    />
                @endforeach
            </ul>
        </div>
    @endif

    <x-slot:mainViewContent>
        @if (filled($selectedItemPath) && !$isSelectedItemDirectory($selectedItemPath))
            
            {{-- @livewire('inspirecms-support::file-preview', [
                'schema' => $this->getSelectedFileItemFormSchema(),
                'actions' => $this->getSelectedFileItemFormActions(),
                'data' => $this->mutateSelectedItemFormDataToFill($selectedItemPath),
                'redirectUrl' => $this->getUrl(),
                'pathStatePath' => $this->getPathStatePath(),
                'contentStatePath' => $this->getContentStatePath(),
            ], key($this->filePreviewComponentKey)) --}}
            <form wire:submit="saveSelectedItem">
                <div class="pb-4">
                    {{ $this->selectedFileItemForm }}
                </div>
                <x-filament-panels::form.actions
                    :actions="$this->getSelectedFileItemFormActions()"
                />
            </form>
            <x-filament-actions::modals />

        @elseif (filled($selectedItemPath) && $isSelectedItemDirectory($selectedItemPath))
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