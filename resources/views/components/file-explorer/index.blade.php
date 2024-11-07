
@props(['items' => []])
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
        {{ $slot }}
    </x-slot:mainViewContent>
    
</x-inspirecms-support::tree-node>