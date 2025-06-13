@props(['items' => []])

<x-inspirecms-support::tree-node class="model-explorer">

    <x-slot:sidebar>

        <x-inspirecms-support::tree-node.model-explorer
            :items="$items" 
            :model-explorer="$modelExplorer"
        />
        
    </x-slot:sidebar>

    <x-slot:main>
        
        @foreach ($this->selectedModelItemKeys as $item)
            {{ $item }}
        @endforeach

    </x-slot:main>

</x-inspirecms-support::tree-node>