@php
    $statePath = $getStatePath();
    $isMultiple = $isMultiple();
    $stateNodeKey = $getStartNode();
    $mediaLibraryFilter = [
        'type' => $getFilterTypes(),
    ];
    // fill selectedMediaId with the state value
    $modelableConfig = [
        'selectedMediaId' => 'state',
    ];
    $filterFormConfig = [
        'invisible' => [
            'type',
        ],
    ]
@endphp
<div x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }} }">
    <livewire:inspirecms-support::media-library
        :parentKey="$stateNodeKey"
        :isMultiple="$isMultiple"
        :filter="$mediaLibraryFilter"
        :modelableConfig="$modelableConfig"
        :filterFormConfig="$filterFormConfig"
    />
</div>