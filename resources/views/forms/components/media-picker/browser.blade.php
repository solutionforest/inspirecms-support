@php
    $statePath = $getStatePath();
    $isMultiple = $isMultiple();
    $mimeTypes = $getMimeTypes();
    $stateNodeKey = $getStartNode();
    $mediaLibraryFilter = [
        'mime_type' => $mimeTypes,
    ];
    // fill selectedMediaId with the state value
    $modelableConfig = [
        'selectedMediaId' => 'state',
    ];
@endphp
<div x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }} }">
    <livewire:inspirecms-support::media-library
        :parentKey="$stateNodeKey"
        :isMultiple="$isMultiple"
        :filter="$mediaLibraryFilter"
        :modelableConfig="$modelableConfig"
    />
</div>