@props([
    'livewire',
    'node' => [],
    'indent' => 0,
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
    @if($enableSelection)
        wire:click="toggleNodeSelection('{{ $nodeId }}')"
    @endif
    {{ 
        $attributes 
            ->class([
                'tree-node-item group relative',
                'cursor-pointer' => $enableSelection && $canSelectNode,
                'opacity-50 cursor-not-allowed' => $enableSelection && !$canSelectNode,
                'is-expanded' => $isExpanded,
                'is-active' => $isSelected,
            ])
    }}
>
    <div 
        class="tree-node-content"
    >
        {{-- Expand/Collapse Button --}}
        <div class="tree-node-toggle">
            @if($node['has_children'] ?? false)
                <x-filament::icon-button
                    color="gray"
                    :icon="\Filament\Support\Icons\Heroicon::ChevronRight"
                    wire:click.stop="toggleNode('{{ $nodeId }}')"
                    :label="$isExpanded ? 'Collapse' : 'Expand'"
                    class="tree-toggle-btn rtl:rotate-180 group-[.is-expanded]:rotate-90 group-[.is-expanded]:rtl:rotate-90"
                    wire:loading.class="opacity-50"
                />
            @else
                <div class="h-5 w-5"></div>
            @endif
        </div>

        {{-- Node Icon --}}
        @if($node['icon'] ?? null)
            <div class="tree-node-icon">
                {{ \Filament\Support\generate_icon_html(icon: $node['icon'], size: \Filament\Support\Enums\IconSize::Small) }}
            </div>
        @endif

        {{-- Node Name --}}
        <div 
            class="tree-node-label"
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
                    onclick="event.stopPropagation()"
                >
                    <span class="tree-node-label-title group-hover:text-primary-600 dark:group-hover:text-primary-400">
                        {{ $nodeTitle }}
                    </span>
                    @if($nodeDescription)
                        <span class="tree-node-label-description">
                            {{ $nodeDescription }}
                        </span>
                    @endif
                </a>
            @else
                <span class="tree-node-label-title">
                    {{ $nodeTitle }}
                </span>
                @if($nodeDescription)
                    <span class="tree-node-label-description">
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
                x-on:click.stop=""
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
    </div>
</div>

{{-- Render Children --}}
@if($isExpanded && ($node['has_children'] ?? false))
    <div class="tree-node-items tree-children" @style(['--tree-node-indent:' . $indent + 1])>
        @foreach($livewire?->getChildrenForNode($nodeId) ?? [] as $childNode)
            <x-inspirecms-support::tree-node.service-side-tree.item
                :node="$childNode"
                :indent="$indent + 1"
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