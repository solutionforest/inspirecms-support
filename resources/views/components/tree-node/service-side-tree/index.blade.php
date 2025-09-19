@props([
    'livewire',
    'nodes' => [],
    'toolbarActions' => [],
    'hasNodeActions' => false,
    'enableSelection' => false,
    'multipleSelection' => true,
    'enableNodeUrls' => true,
    'maxSelections' => null,
])

<div {{ $attributes
        ->class(['server-side-tree'])
}}>
    {{-- Toolbar Actions --}}
    @if(!empty($toolbarActions))
        <div
            class="tree-toolbar px-3"
        >
            @foreach($toolbarActions as $action)
                {{$action}}
            @endforeach
        </div>
    @endif

    <div class="space-y-1">
        @if(empty($nodes))
            <div class="tree-empty text-sm text-gray-500 dark:text-gray-400 py-4 text-center">
                No items found
            </div>
        @else
            @foreach($nodes as $node)
                <x-inspirecms-support::tree-node.service-side-tree.item
                    :node="$node"
                    :livewire="$livewire"
                    :hasActions="$hasNodeActions"
                    :enableSelection="$enableSelection"
                    :multipleSelection="$multipleSelection"
                    :enableNodeUrls="$enableNodeUrls"
                    :maxSelections="$maxSelections"
                />
            @endforeach
        @endif
    </div>
</div>