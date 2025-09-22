@props([
    'level' => 1,
    'nodeVariable' => 'node',
    'indexVariable' => 'index',
    'parentId' => 'null',
    'maxDepth' => -1, // -1 means unlimited
    'hasActions' => false,
    'livewire' => null,
])

@php
    $livewireId = $livewire ? $livewire->getId() : 'no-livewire';
    $treeNodeActionsLivewireId = "{$livewireId}-tree-node-{$nodeVariable}-actions";
@endphp

<div>
    <!-- Drop indicator before node -->
    <template x-if="dropTargetIndex === {{ $indexVariable }} && dropTargetParent === {{ $parentId }} && dropPosition === 'before'">
        <x-inspirecms-support::tree-node.drop-indicator />
    </template>

    <!-- Node -->
    <div :id="'node-' + {{ $nodeVariable }}.id"
        :class="{
            'tree-node': true, 
            'active': selectedNode === {{ $nodeVariable }}.id,
            'search-match': nodeMatchesSearch({{ $nodeVariable }}),
            'dragover': dropPosition === 'inside' && dropTargetParent === {{ $nodeVariable }}.id,
            'dragover-before': dropPosition === 'before' && dropTargetIndex === {{ $indexVariable }} && dropTargetParent === {{ $parentId }},
            'dragover-after': dropPosition === 'after' && dropTargetIndex === {{ $indexVariable }} && dropTargetParent === {{ $parentId }},
            'beyond-max-depth': !isNodeVisible({{ $nodeVariable }})
        }"
        @click.stop="selectNode({{ $nodeVariable }}.id)"
        draggable="true"
        @dragstart="dragStart($event, {{ $nodeVariable }}.id)"
        @dragover.prevent="dragOver($event, {{ $indexVariable }}, {{ $parentId }})"
        @dragleave="dragLeave($event)"
        @drop.prevent="drop($event, {{ $indexVariable }}, {{ $parentId }})"
        @dragend="dragEnd()"
        tabindex="0"
        role="treeitem"
        :aria-expanded="{{ $nodeVariable }}.children?.length ? {{ $nodeVariable }}.expanded : undefined"
        :aria-selected="selectedNode === {{ $nodeVariable }}.id"
        :aria-level="{{ $level }}"
        @focus="lastFocusedNode = {{ $nodeVariable }}.id"
        {{ $attributes }}
    >
        
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <!-- Expand/Collapse indicator -->
                <span x-show="{{ $nodeVariable }}.children && {{ $nodeVariable }}.children.length > 0" 
                    :class="{{ $nodeVariable }}.expanded ? 'expanded-indicator' : 'collapsed-indicator'"
                    @click.stop="toggleNode({{ $nodeVariable }}.id)"></span>
                
                <div class="flex-1 flex flex-col">

                    <!-- Node text -->
                    <span x-html="formatNodeText({{ $nodeVariable }})"></span>
                    
                    <!-- Node description -->
                    <span x-show="{{ $nodeVariable }}.description" 
                        class="text-xs text-gray-700 dark:text-gray-400 truncate max-w-[12rem] md:max-w-[7rem] lg:max-w-full"
                        x-html="{{ $nodeVariable }}.description"></span>
                </div>
                
            </div>

            {{-- Node Actions --}}
            @if ($hasActions)
                @php
                    $alpineNodeIdVar = $nodeVariable . '.id';
                @endphp
                <x-filament::actions 
                    class="tree-node-actions"
                    wire:key={{ $treeNodeActionsLivewireId }}
                    x-bind:wire:target="getNodeItemActionsHtml({{ $alpineNodeIdVar }})"
                    x-data="{
                        actions: [],
                        init() {
                            $nextTick(async () => {
                                this.actions = await $wire.getNodeItemActionsHtml({{ $alpineNodeIdVar }});
                            });
                        }
                    }"
                >
                    <div wire:loading x-bind:wire:target="getNodeItemActionsHtml({{ $alpineNodeIdVar }})" class="animate-spin">
                        <x-filament::loading-indicator class="h-4 w-4" />
                    </div>
                    <template x-for="action in actions">
                        <div x-html="action"></div>
                    </template>
                </x-filament::actio>
            @endif
        </div>
    </div>

    <!-- Drop indicator after node -->
    <template x-if="dropTargetIndex === {{ $indexVariable }} && dropTargetParent === {{ $parentId }} && dropPosition === 'after'">
        <x-inspirecms-support::tree-node.drop-indicator />
    </template>

    <!-- Children nodes - recursive rendering -->
    <div x-show="{{ $nodeVariable }}.expanded && {{ $nodeVariable }}.children && {{ $nodeVariable }}.children.length > 0" 
        class="tree-children" 
        :key="{{ $nodeVariable }}.id + '-children'">
        
        @php
            $childLevel = $level + 1;
            $shouldRender = 
                (
                    $maxDepth === -1
                    && $level < 25 // Prevent infinite recursion in case of circular references
                )
                || $maxDepth != -1 && $childLevel <= $maxDepth;
            $childNodeVar = "node_level{$childLevel}";
            $childIndexVar = "index_level{$childLevel}";
            $parentIdVar = "{$nodeVariable}.id";
        @endphp
        @if($shouldRender)
            <template x-for="({{ $childNodeVar }}, {{ $childIndexVar }}) in {{ $nodeVariable }}.children" :key="{{ $childNodeVar }}.id + '-' + {{ $childIndexVar }}">
                <x-inspirecms-support::tree-node.recursive-node
                    :level="$childLevel"
                    :nodeVariable="$childNodeVar"
                    :indexVariable="$childIndexVar"
                    :parentId="$parentIdVar"
                    :maxDepth="$maxDepth"
                    :hasActions="$hasActions"
                    :livewire="$livewire"
                />
            </template>
        @endif
    </div>
</div>