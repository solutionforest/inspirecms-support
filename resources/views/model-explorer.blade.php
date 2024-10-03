@php
    $items = $this->getGroupedNodeItems();
@endphp

<x-inspirecms-support::model-explorer :items="$items">
    {{ $this->selectedModelItemKey }}
</x-inspirecms-support::model-explorer>