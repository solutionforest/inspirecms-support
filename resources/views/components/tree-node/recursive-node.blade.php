@props([
    'level' => 1,
    'nodeVariable' => 'node',
    'indexVariable' => 'index',
    'parentId' => 'null',
    'actions' => [],
    'maxDepth' => -1, // -1 means unlimited
])

@php
    // Configure tree node actions
    // Use pass actions to display, but change click action with Alpine nodeId
    $treeNodeActions = collect($actions)
        ->flatten()
        ->whereInstanceOf(\Filament\Actions\Action::class)
        ->where(fn ($action) => $action->isVisible())
        ->map(fn ($action) => $action
            ->alpineClickHandler("\$wire.{$action->getName()}TreeNode({$nodeVariable}.id)")
        )
        ->all();
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
        {{ $attributes }}>
        
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

            <!-- Node actions -->
            <div class="flex items-center gap-x-2">
                @foreach ($treeNodeActions as $action)
                    {{ $action }}
                @endforeach
            </div>
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
                    :actions="$actions"
                    :maxDepth="$maxDepth"
                />
            </template>
        @endif
    </div>
</div>