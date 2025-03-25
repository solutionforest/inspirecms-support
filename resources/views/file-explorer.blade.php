@php
    $items = $this->getGroupedNodeItems();
    $selectedItemPath = $this->fileExplorerSelectedPath;
@endphp
<x-inspirecms-support::file-explorer :items="$items">
    @if (filled($selectedItemPath) && !$isSelectedItemDirectory($selectedItemPath))
        <p class="pb-4">{{ $selectedItemPath }}</p>
        <div class="h-80 px-4 py-1.5 rounded-lg shadow-lg ring-1 ring-gray-300 bg-white dark:bg-gray-700 dark:ring-white/10">
            <p class="h-full overflow-y-auto">
                {{ $this->getFileContent($selectedItemPath) }}
            </p>
        </div>

    @elseif (filled($selectedItemPath) && $isSelectedItemDirectory($selectedItemPath))
        <p class="text-gray-500 non-selectable-text">
            @lang('inspirecms-support::tree-node.messages.selected_item_is_directory')
        </p>
    @else
        <p class="text-gray-500 non-selectable-text">
            @lang('inspirecms-support::tree-node.messages.select_file_to_view')
        </p>
    @endif

    <x-inspirecms-support::tree-node.actions.modals />

</x-inspirecms-support::tree-node>