@php
    use Illuminate\Database\Eloquent\Model;
    use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
    
    $paginator = $this->assets;
    $folders = collect($paginator->items())->where(fn (Model | MediaAsset $item) => $item->isFolder());
    $media = collect($paginator->items())->where(fn (Model | MediaAsset $item) => !$item->isFolder());

    $loadingIndicator = [
        'count' => 3,
        'columns' => [
            'lg' => 4,
        ],
    ];
    $loadingIndicatorTargets = implode(',', ['clearCache', 'updating', 'gotoPage', 'resetPage', 'nextPage', 'setPage']);
@endphp

<div class="media-library"
    @if ($this->isMediaPickerModal())
        x-data="{
            selectedMediaId: $wire.entangle('selectedMediaId').live,
        }" 
        x-modelable="selectedMediaId" 
        x-model="selected"
    @endif
>

    <div class="media-library__header">
        <x-inspirecms-support::media-library.breadcrumbs :breadcrumbs="$this->getBreadcrumbs()" />
        <div class="media-library__header__actions">
            @foreach ($this->getVisibleHeaderActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </div>

    <div class="media-library__content">
        
        @if (!$this->isUnderRoot())
            <div class="ctn folder-ctn">
                <livewire:inspirecms-support::media-library.folders :$parentKey />
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
            </div>
            <div class="browser-items-ctn ctn">
                <div class="browser-items-grid-ctn">
                    <h4>Folders</h4>
                    <div class="w-full" wire:loading wire:target="{{ $loadingIndicatorTargets }}">
                        <x-inspirecms-support::media-library.loading-section :count="$loadingIndicator['count']" :columns="$loadingIndicator['columns']" />
                    </div>
                    <div class="browser-items-grid" wire:loading.remove wire:target="{{ $loadingIndicatorTargets }}">
                        @foreach ($folders ?? [] as $item)
                            <x-inspirecms-support::media-library.browser-item 
                                :media-item="$item" 
                                :actions="$this->getCachedMediaItemActions()" 
                                :selectable="!$this->isMediaPickerModal()"
                            />
                        @endforeach
                    </div>
                </div>
                <div class="browser-items-grid-ctn">
                    <h4>Media</h4>
                    <div class="w-full" wire:loading wire:target="{{ $loadingIndicatorTargets }}">
                        <x-inspirecms-support::media-library.loading-section :count="$loadingIndicator['count']" :columns="$loadingIndicator['columns']" />
                    </div>
                    <div class="browser-items-grid" wire:loading.remove wire:target="{{ $loadingIndicatorTargets }}">
                        @foreach ($media ?? [] as $item)
                            <x-inspirecms-support::media-library.browser-item :media-item="$item" :actions="$this->getCachedMediaItemActions()" />
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