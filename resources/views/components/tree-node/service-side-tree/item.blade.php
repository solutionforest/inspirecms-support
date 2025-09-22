@props([
    'livewire',
    'node' => [],
    'hasActions' => false,
    'enableSelection' => false,
    'multipleSelection' => true,
    'enableNodeUrls' => true,
    'maxSelections' => null,
])

@php
    $nodeId = $node['id'] ?? null;
    $isExpanded = $livewire->isNodeExpanded($nodeId);
    $isSelected = $enableSelection ? $livewire->isNodeSelected($nodeId) : false;
    $canSelectNode = $enableSelection ? $livewire->canSelectNode($nodeId) : false;
    $livewireId = $livewire->getId();

    $treeNodeLivewireId = "{$livewireId}-tree-node-{$nodeId}";
    $treeNodeActionsLivewireId = "{$treeNodeLivewireId}-actions";
@endphp

<div 
    wire:key={{ $treeNodeLivewireId }}
    class=""
    style="padding-left: {{ ($node['depth'] ?? 0) * 1.2 }}rem;"
    @if($enableSelection)
        wire:click="toggleNodeSelection('{{ $nodeId }}')"
    @endif
    @class([
        'tree-node-item group relative',
        'cursor-pointer' => $canSelectNode,
        'opacity-50 cursor-not-allowed' => !$canSelectNode,
    ])
>
    <div 
        @class([
            'tree-node-content flex items-center gap-x-2 rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5',
            'bg-primary-50 dark:bg-primary-900/20' => $isSelected,
        ])
    >
        {{-- Expand/Collapse Button --}}
        <div class="tree-node-toggle flex-shrink-0">
            @if($node['has_children'] ?? false)
                <button
                    type="button"
                    wire:click="toggleNode('{{ $nodeId }}')"
                    class="tree-toggle-btn flex h-5 w-5 items-center justify-center rounded text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                    aria-label="{{ $isExpanded ? 'Collapse' : 'Expand' }}"
                >
                    @if($isExpanded)
                        <x-heroicon-s-chevron-down class="h-4 w-4" />
                    @else
                        <x-heroicon-s-chevron-right class="h-4 w-4" />
                    @endif
                </button>
            @else
                <div class="h-5 w-5"></div>
            @endif
        </div>

        {{-- Node Icon --}}
        @if($node['icon'] ?? null)
            <div class="tree-node-icon flex-shrink-0">
                @if(str_starts_with($node['icon'], 'heroicon'))
                    <x-dynamic-component 
                        :component="$node['icon']" 
                        class="h-5 w-5 text-gray-500 dark:text-gray-400" 
                    />
                @else
                    <div class="h-5 w-5 text-gray-500 dark:text-gray-400">
                        {!! $node['icon'] !!}
                    </div>
                @endif
            </div>
        @endif

        {{-- Node Name --}}
        <div 
            class="tree-node-label flex-1 min-w-0"
        >
            @php
                $nodeLabelData = $livewire->getNodeLabel($node);
                $nodeTitle = $nodeLabelData['title'] ?? $node['title'] ?? 'Untitled';
                $nodeDescription = $nodeLabelData['description'] ?? $node['description'] ?? null;
                $nodeUrl = $enableNodeUrls ? ($livewire->getNodeUrl($node) ?? $node['url'] ?? null) : null;
            @endphp
            
            @if($nodeUrl)
                <a 
                    href="{{ $nodeUrl }}" 
                    class="block group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-150"
                >
                    <span class="block truncate text-sm font-medium text-gray-950 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400">
                        {{ $nodeTitle }}
                    </span>
                    @if($nodeDescription)
                        <span class="block truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $nodeDescription }}
                        </span>
                    @endif
                </a>
            @else
                <span class="block truncate text-sm font-medium text-gray-950 dark:text-white">
                    {{ $nodeTitle }}
                </span>
                @if($nodeDescription)
                    <span class="block truncate text-xs text-gray-500 dark:text-gray-400">
                        {{ $nodeDescription }}
                    </span>
                @endif
            @endif
        </div>

        {{-- Node Actions --}}
        @if ($hasActions)
            <x-filament::actions 
                class="tree-node-actions"
                wire:key={{ $treeNodeActionsLivewireId }}
                wire:target="getNodeItemActionsHtml('{{ $nodeId }}')"
                x-data="{
                    actions: [],
                    init() {
                        $nextTick(async () => {
                            this.actions = await $wire.getNodeItemActionsHtml('{{ $nodeId }}');
                        });
                    }
                }"
            >
                <div wire:loading wire:target="getNodeItemActionsHtml('{{ $nodeId }}')" class="animate-spin">
                    <x-filament::loading-indicator class="h-4 w-4" />
                </div>
                <template x-for="action in actions">
                    <div x-html="action"></div>
                </template>
            </x-filament::actions>
        @endif

        {{-- Loading Indicator --}}
        <div 
            wire:loading 
            wire:target="toggleNode('{{ $nodeId }}')" 
            class="absolute left-50 flex items-center justify-center bg-white/75 dark:bg-gray-900/75"
        >
            <x-filament::loading-indicator class="h-4 w-4" />
        </div>
    </div>
</div>

{{-- Render Children --}}
@if($isExpanded && ($node['has_children'] ?? false))
    <div class="tree-children">
        @foreach($livewire?->getChildrenForNode($nodeId) ?? [] as $childNode)
            <x-inspirecms-support::tree-node.service-side-tree.item
                :node="$childNode"
                :livewire="$livewire"
                :hasActions="$hasActions"
                :enableSelection="$enableSelection"
                :multipleSelection="$multipleSelection"
                :enableNodeUrls="$enableNodeUrls"
                :maxSelections="$maxSelections"
            />
        @endforeach
    </div>
@endif