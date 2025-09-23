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
            class="tree-toolbar"
        >
            @foreach($toolbarActions as $action)
                {{$action}}
            @endforeach
        </div>
    @endif

    <div class="tree-content-ctn">
        {{-- Navigation Header --}}
        @if($showNavigationHeader)
            <div class="tree-navigation-header">
                <div class="tree-navigation-header-text-ctn">
                    @if (isset($indexUrl) && filled($indexUrl))
                        <a 
                            href="{{ $indexUrl }}" 
                            class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                            title="Return to Index"
                        >
                            <x-heroicon-s-home class="icon" />
                            <span>{{ $homeButtonText }}</span>
                        </a>
                    @else
                        <span>
                            <x-heroicon-s-home class="icon" />
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
        @endif

        @if(empty($nodes))
            <div class="tree-empty">
                No items found
            </div>
        @else
            <div class="tree-node-items">
                @foreach($nodes as $node)
                    <x-inspirecms-support::tree-node.service-side-tree.item
                        :node="$node"
                        :indent="0"
                        :livewire="$livewire"
                        :hasActions="$hasNodeActions"
                        :enableSelection="$enableSelection"
                        :multipleSelection="$multipleSelection"
                        :enableNodeUrls="$enableNodeUrls"
                        :maxSelections="$maxSelections"
                    />
                @endforeach
            </div>
        @endif
    </div>
</div>