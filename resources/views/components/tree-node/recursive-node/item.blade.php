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

<div {{ $attributes->class(['tree-node-item-ctn']) }}>
    <!-- Drop indicator before node -->
    <template x-if="dropTargetIndex === {{ $indexVariable }} && dropTargetParent === {{ $parentId }} && dropPosition === 'before'">
        <x-inspirecms-support::tree-node.drop-indicator />
    </template>

    <!-- Node -->
    <div :id="'node-' + {{ $nodeVariable }}.id"
        class="tree-node-item group"
        :class="{
            'active': selectedNode === {{ $nodeVariable }}.id,
            'search-match': nodeMatchesSearch({{ $nodeVariable }}),
            'dragover': dropPosition === 'inside' && dropTargetParent === {{ $nodeVariable }}.id,
            'dragover-before': dropPosition === 'before' && dropTargetIndex === {{ $indexVariable }} && dropTargetParent === {{ $parentId }},
            'dragover-after': dropPosition === 'after' && dropTargetIndex === {{ $indexVariable }} && dropTargetParent === {{ $parentId }},
            'beyond-max-depth': !isNodeVisible({{ $nodeVariable }}),
            'is-expanded': {{ $nodeVariable }}.expanded,
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
    >
        <div class="tree-node-content">
            <!-- Expand/Collapse indicator -->
            <x-filament::icon-button
                color="gray"
                :icon="\Filament\Support\Icons\Heroicon::ChevronRight"
                x-show="{{ $nodeVariable }}.children && {{ $nodeVariable }}.children.length > 0" 
                @click.stop="toggleNode({{ $nodeVariable }}.id)"
                class="tree-toggle-btn rtl:rotate-180 group-[.is-expanded]:rotate-90 group-[.is-expanded]:rtl:rotate-90"
            />
            
            <div class="tree-node-label">

                <!-- Node text -->
                <span x-html="formatNodeText({{ $nodeVariable }})" class="tree-node-label-title"></span>
                
                <!-- Node description -->
                <span x-show="{{ $nodeVariable }}.description" 
                    class="tree-node-label-description"
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
                wire:key="{{ $treeNodeActionsLivewireId }}"
                x-data="{
                    actions: [],
                    init() {
                        $nextTick(async () => {
                            this.actions = await $wire.getNodeItemActionsHtml({{ $alpineNodeIdVar }});
                        });
                    }
                }"
            >
                <template x-for="action in actions">
                    <div x-html="action"></div>
                </template>
            </x-filament::actio>
        @endif
    </div>

    <!-- Drop indicator after node -->
    <template x-if="dropTargetIndex === {{ $indexVariable }} && dropTargetParent === {{ $parentId }} && dropPosition === 'after'">
        <x-inspirecms-support::tree-node.drop-indicator />
    </template>

    <!-- Children nodes - recursive rendering -->
    <div x-show="{{ $nodeVariable }}.expanded && {{ $nodeVariable }}.children && {{ $nodeVariable }}.children.length > 0" 
        class="tree-node-items tree-children" 
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
                <x-inspirecms-support::tree-node.recursive-node.item
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