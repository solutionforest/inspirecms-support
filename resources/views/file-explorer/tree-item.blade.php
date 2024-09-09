<div class="file-explorer-item" style="padding-left:{{ $item->level * 8 }}px">
    <div class="flex items-center cursor-pointer gap-2 py-1 hover:bg-gray-100 overflow-hidden" wire:click="toggleExpand">
        
        <div class="flex items-center gap-1">
            <span class="w-4">
                @if ($this->hasChildren())
                    @if ($isExpandLoading)
                        <svg class="animate-spin h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    @else
                        <x-icon name="{{ $isExpanded ? 'heroicon-o-chevron-down' : 'heroicon-o-chevron-right' }}" class="w-4 h-4" />
                    @endif
                @endif
            </span>
    
            <x-icon :name="$this->getIcon()" class="w-5 h-5" />
        </div>
        
        <span class="font-medium truncate max-w-[calc(100%-40px)] non-selectable-text">{{ $item->name }}</span>
        
    </div>

    @if ($this->hasChildren() && $isExpanded)
        <div class="children">
            @if ($children !== null)
                @foreach ($children as $child)
                    <livewire:file-explorer-item :item="$child" :wire:key="$child->path" />
                @endforeach
            @else
                <div class="pl-9 py-2 text-gray-500 non-selectable-text">@lang('inspirecms-support::tree-node.loading')</div>
            @endif
        </div>
    @endif
</div>