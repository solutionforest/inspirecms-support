@php
    use Illuminate\Database\Eloquent\Model;
    use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
    
    $paginator = $this->assets;

    $loadingIndicator = [
        'count' => 3,
        'columns' => [
            'lg' => 4,
        ],
    ];
    $loadingIndicatorTargets = implode(',', [
        'assets', 
        'parentKey',

        'filter',
        'sort',
        
        'clearCache', 
        //'updating', 

        'gotoPage', 
        'resetPage', 
        'nextPage', 
        'setPage',
    ]);

    $livewireKey = $this->getId();
    $alpineData = collect([
        
    ])->when($this->isMediaPickerModal(), function ($collection) {
        return $collection->merge([
            'selectedMediaId: $wire.entangle(\'selectedMediaId\').live',
        ]);
    })->implode(', ');
@endphp

<div @class([
        'media-library',
        'media-library--picker' => $this->isMediaPickerModal(),
        'media-library--detail-expanded' => $this->hasAnyMediaSelected(),
    ])
    x-data="{ {{ $alpineData }} }"
    @if ($this->isMediaPickerModal())
        x-modelable="selectedMediaId" 
        x-model="selected"
    @endif
>

    <div class="media-library__header">
        <x-inspirecms-support::media-library.breadcrumbs :breadcrumbs="$breadcrumbs" />
        <div class="media-library__header__actions">
            @foreach ($this->getVisibleHeaderActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </div>

    <div class="media-library__content">
        
        @if (!$this->isUnderRoot())
            <div class="ctn folder-ctn" 
                x-data="{ expanded: false }"
                x-bind:class="{ 'expanded': expanded }"
                x-on:click.outside="expanded = false"
            >
                <div class="trigger" 
                    x-on:click="expanded = !expanded">
                    <button type="button" 
                        class="p-4 rounded-full shadow-md bg-white ring-1 ring-gray-300 hover:bg-gray-100 dark:bg-gray-600 dark:ring-gray-400/20 dark:hover:text-gray-400" 
                        label="Expand folder"
                    >
                        <x-filament::icon 
                            icon="heroicon-o-folder-open" 
                            class="h-5 w-5" 
                        />
                    </button>
                </div>
                <div class="folder-ctn__main">
                    <livewire:inspirecms-support::media-library.folders :folders="$this->folders" :$parentKey />
                </div>
            </div>
        @endif

        <div class="ctn browser-ctn">
            <div class="filter-ctn ctn">
                <form id="filterForm" method="post">
                    {{ $this->filterForm }}
                </form>
                <form id="sortForm" method="post">
                    {{ $this->sortForm }}
                </form>
                <x-filament::icon-button 
                    wire:click="resetAll"
                    icon="heroicon-o-arrow-path"
                    size="sm"
                    color="gray"
                    label="Refresh"
                />
            </div>
            <div class="browser-items-ctn ctn">
                <div class="browser-items-grid-ctn">
                    <h4>{{ __('inspirecms-support::media-library.folder.plural') }}</h4>
                    <div class="w-full" wire:loading wire:target="{{ $loadingIndicatorTargets }}">
                        <x-inspirecms-support::media-library.loading-section :count="$loadingIndicator['count']" :columns="$loadingIndicator['columns']" />
                    </div>
                    <div class="browser-items-grid" wire:loading.remove wire:target="{{ $loadingIndicatorTargets }}">
                        @foreach (collect($paginator->items())->where(fn (Model | MediaAsset $item) => $item->isFolder()) ?? [] as $item)
                            <x-inspirecms-support::media-library.browser-item 
                                :livewire-key="$livewireKey"
                                :media-item="$item" 
                                :actions="$this->getCachedMediaItemActions()" 
                                :selectable="!$this->isMediaPickerModal()"
                                :is-draggable="$this->canDragAndDrop()"
                            />
                        @endforeach
                    </div>
                </div>
                <div class="browser-items-grid-ctn">
                    <h4>{{ __('inspirecms-support::media-library.media.plural') }}</h4>
                    <div class="w-full" wire:loading wire:target="{{ $loadingIndicatorTargets }}">
                        <x-inspirecms-support::media-library.loading-section :count="$loadingIndicator['count']" :columns="$loadingIndicator['columns']" />
                    </div>
                    <div class="browser-items-grid" wire:loading.remove wire:target="{{ $loadingIndicatorTargets }}">
                        @foreach (collect($paginator->items())->where(fn (Model | MediaAsset $item) => !$item->isFolder()) ?? [] as $item)
                            <x-inspirecms-support::media-library.browser-item 
                                :livewire-key="$livewireKey"
                                :media-item="$item" 
                                :actions="$this->getCachedMediaItemActions()" 
                                :is-draggable="$this->canDragAndDrop()"
                            />
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if ($this->hasAnyMediaSelected())
            <div class="ctn detail-info-ctn">
                <livewire:inspirecms-support::media-library.detail-info :$selectedMediaId :$toggleMediaId :$isModalPicker />
            </div>
        @endif
    </div>

    <div class="media-library__footer">
        @isset($paginator)
            <x-filament::pagination 
                :paginator="$paginator"
                :page-options="$pageOptions"
                current-page-option-property="perPage"
                extreme-links
            />
        @endisset
    </div>

    <x-filament-actions::modals />
    
</div>
