@php
    $statePath = $getStatePath();
    $isMultiple = $isMultiple();
    $stateNodeKey = $getStartNode();
    $filterTypes = $getFilterTypes();
    $mediaLibraryFilter = [
        'type' => $filterTypes,
    ];
    // fill selectedMediaId with the state value
    $modelableConfig = [
        'selectedMediaId' => 'state',
    ];
    // hide the type filter if there is only one type
    $formConfig = [
        'upload' => [
            'collap_open' => false,
        ],
        'filter' => [
            'collap_open' => true,
        ],
        'sort' => [
            'collap_open' => true,
        ],
    ];
    if (count($filterTypes) === 1) {
        $formConfig['filter']['invisible_columns'] = ['type'];
    }
@endphp
<div x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }} }">
    <livewire:inspirecms-support::media-library
        :parentKey="$stateNodeKey"
        :isMultiple="$isMultiple"
        :filter="$mediaLibraryFilter"
        :modelableConfig="$modelableConfig"
        :formConfig="$formConfig"
    />
</div>