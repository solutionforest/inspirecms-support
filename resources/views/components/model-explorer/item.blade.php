@props([
    'id',
    'name',
    'level',
    'children',
    'hasChildren' => false,
    'actions' => [],
])
<div class="model-explorer-item" style="padding-left:{{ $level * 8 }}px;">
    <div class="flex items-center py-1 {{ $selectedModelId == $id ? 'bg-blue-100' : '' }}  hover:bg-gray-100">
        <div 
            class="flex items-center justify-start gap-2 w-full overflow-hidden cursor-pointer"
            wire:click="toggleItem({{ $id }})"
        >
            <span class="mr-2 w-4">
                @if ($hasChildren)
                    @if (!in_array($id, $expandedItems))
                        <x-icon name="heroicon-o-chevron-right" class="w-4 h-4" />
                    @else
                        <x-icon name="heroicon-o-chevron-down" class="w-4 h-4" />
                    @endif
                @endif
            </span>
            <span class="font-medium truncate max-w-[calc(100%-40px)] non-selectable-text" wire:click.stop="selectModel({{ $id }})">
                {{ $name }}
            </span>
        </div>
        <div class="px-2">
            
            @if (!empty($actions))
                <x-filament-actions::group
                    :actions="$actions"
                    label="{{__('inspirecms-support::tree-node.actions')}}"
                    icon="heroicon-m-ellipsis-vertical"
                    color="primary"
                    size="md"
                    tooltip="{{__('inspirecms-support::tree-node.more_actions')}}"
                    dropdown-placement="bottom-start"
                    dropdown-width="xs"
                />
                <x-filament-actions::modals />
            @endif
        </div>
    </div>
    @if (in_array($id, $expandedItems) && !empty($children))
        <div>
            @foreach ($children as $child)
                @include('inspirecms-support::components.model-explorer.item', [
                    'name' => $child['name'], 
                    'id' => $child['id'], 
                    'hasChildren' => $child['hasChildren'], 
                    'children' => $child['children'], 
                    'level' => $level + 1,
                    'actions' => $this->getItemActions($child),
                ])
            @endforeach
        </div>
    @endif
</div>