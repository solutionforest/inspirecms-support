<div class="tree-viewer-layout model-explorer flex min-h-screen"
    x-ignore
    ax-load
    x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('tree-node', 'solution-forest/inspirecms-support'))]"
    ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('tree-node', 'solution-forest/inspirecms-support') }}"
    x-data="treeNodeComponent()">
    <div 
        class="tree-viewer-sidebar flex-shrink-0 overflow-y-auto border-r border-gray-200" 
        style="max-height: calc(100vh - 2rem); width: 256px;"
    >
        @if ($items->isEmpty())
            <p>@lang('inspirecms-support::tree-node.no_models_found')</p>
        @else
            @foreach ($items as $item)
                @include('inspirecms-support::components.model-explorer.item', [
                    'name' => $item['name'], 
                    'id' => $item['id'], 
                    'hasChildren' => $item['hasChildren'], 
                    'children' => $item['children'], 
                    'level' => 0,
                    'actions' => $this->getItemActions($item),
                ])
            @endforeach
        @endif
    </div>
    <div 
        class="tree-viewer-resizer cursor-col-resize w-1 bg-gray-200 hover:bg-gray-300 active:bg-gray-400"
    ></div>
    
    <div class="tree-viewer-main flex-grow overflow-auto p-4">
        <div wire:key="selected-model-details">
            @if ($selectedModelData)
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pb-4">
                    <h3 class="text-lg font-semibold mb-2">@lang('inspirecms-support::tree-node.model_details'): {{ $selectedModelData['name'] }}</h3>
                    @php
                        $actions = $this->getItemActions($selectedModelData);
                    @endphp
                    @if (!empty($actions))
                        <div>
                            @foreach ($actions as $action)
                                {{ $action }}
                            @endforeach
                            <x-filament-actions::modals />
                        </div>
                    @endif
                </div>
                <x-filament::section>
                    {{-- <pre class="bg-gray-100 p-4 rounded overflow-auto max-h-[calc(100vh-10rem)]">{{ json_encode($selectedModelData, JSON_PRETTY_PRINT) }}</pre> --}}
                    {{ $this->selectedDataInfolist }}
                </x-filament::section>
            @else
                <p class="text-gray-500 non-selectable-text">@lang('inspirecms-support::tree-node.select_model_to_view')</p>
            @endif
        </div>
    </div>
</div>