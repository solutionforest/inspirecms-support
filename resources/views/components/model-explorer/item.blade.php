@props(['item'])
@php
    $nodeDepth = $item['depth'];
    $nodeKey = $item['key'];
    $hasChildren = $item['hasChildren'];
@endphp
<li x-data="{
    itemKey: @js($nodeKey),
    depth: @js($nodeDepth),
    hasChildren: @js($hasChildren),
}" tabindex="@js($nodeDepth)" data-unique-key="{{ $nodeKey }}" data-treenode>
    <div @click="await toggleItem(itemKey, depth)" class="node">
        <span>{{ $item['label'] ?? null }}</span>
        <span x-show="hasChildren" x-text="isExpanded(itemKey) ? '-' : '+'"></span>
    </div>
    <ul x-show="isExpanded(itemKey)" x-transition {{ $hasChildren ? 'data-subtree' : ''}} @style([
        'padding-left:' . (18 + $nodeDepth) . 'px',
    ])>
        @foreach ($item['children'] ?? [] as $child)
            <x-inspirecms-support::model-explorer.item  
                :item="$child" 
            />
        @endforeach
    </ul>
</li>