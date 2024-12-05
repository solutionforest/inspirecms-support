@php
    $items = $this->getGroupedNodeItems();
@endphp

<x-inspirecms-support::model-explorer :items="$items" :model-explorer="$this->modelExplorer">
    {{ $this->selectedModelItemKey }}
</x-inspirecms-support::model-explorer>