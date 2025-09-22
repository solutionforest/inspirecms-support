@props([
    'livewire',
    'nodes' => [],
    'toolbarActions' => [],
    'navigationHeaderActions' => [],
    'hasNodeActions' => false,
    'showNavigationHeader' => true,
    'enableSelection' => false,
    'multipleSelection' => true,
    'enableNodeUrls' => true,
    'maxSelections' => null,
    'indexUrl' => null,
    'homeButtonText' => 'Home',
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
        {{-- Navigation Header --}}
        @if($showNavigationHeader)
            <div class="tree-navigation-header border-b border-gray-200 dark:border-gray-700 mb-2">
                <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex-1 flex items-center gap-2">
                        @if (isset($indexUrl) && filled($indexUrl))
                            <a 
                                href="{{ $indexUrl }}" 
                                class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                                title="Return to Index"
                            >
                                <x-heroicon-s-home class="h-4 w-4" />
                                <span>{{ $homeButtonText }}</span>
                            </a>
                        @else
                            <span class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <x-heroicon-s-home class="h-4 w-4" />
                                <span>{{ $homeButtonText }}</span>
                            </span>
                        @endif
                    </div>
                    {{-- Toolbar Actions --}}
                    @if(!empty($navigationHeaderActions))
                        <div
                            class="tree-nav-actions"
                        >
                            @foreach($navigationHeaderActions as $action)
                                {{$action}}
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

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