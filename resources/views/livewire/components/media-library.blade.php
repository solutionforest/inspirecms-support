@php
    $formKey = $this->getId() . '.forms.' . $this->getFormStatePathFor('uploadFileForm');
    $modelableConfig = $this->modelableConfig;

    $createFolderAction = $this->getCachedMediaLibraryAction('create-folder');
@endphp
<div class="media-library gap-3"
    ax-load
    ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('media-library-component', 'solution-forest/inspirecms-support') }}"
    x-data="mediaLibraryComponent({
        selectedMediaId: $wire.entangle('selectedMediaId').live,
        isMultiple: $wire.entangle('isMultiple').live,
    })"
    @if (!empty($modelableConfig))
        @php
            $modelable = array_key_first($modelableConfig);
            $model = array_values($modelableConfig)[0] ?? null;
        @endphp
    x-modelable="{{ $modelable }}" x-model="{{ $model }}"
    @endif
    >

    @if ($createFolderAction->isVisible())
        <div class="pb-2 flex gap-2 justify-end">
            {{ $createFolderAction }}
        </div>
    @endif

    @if (static::canCreate())
        <div class="form-container">
            <x-inspirecms-support::media-library.upload-form :livewireKey="$formKey" :is-collapsed="$this->isFormCollapsed('uploadFileForm')">
                {{ $this->uploadFileForm }}
            </x-inspirecms-support::media-library.upload-form>
        </div>
    @endif
    
    <div class="form-container ensure-select-width">
        <div class="flex flex-col gap-y-4 lg:flex-row lg:gap-x-4">
            <x-inspirecms-support::media-library.filter-form :is-collapsed="$this->isFormCollapsed('filterForm')" class="flex-1">
                {{ $this->filterForm }}
            </x-inspirecms-support::media-library.filter-form>
            <x-inspirecms-support::media-library.sort-form :is-collapsed="$this->isFormCollapsed('sortForm')" class="flex-1">
                {{ $this->sortForm }}
            </x-inspirecms-support::media-library.sort-form>
        </div>
    </div>

    <template x-if="isSelectMultiple && selectedMediaId?.length > 0">
        <div>
            <span x-text="selectedMediaId?.length"></span> files selected
        </div>
    </template>

    <div class="media-library__breadcrumbs">
        <x-inspirecms-support::media-library.breadcrumbs :breadcrumbs="$this->getBreadcrumbs()" />
    </div>

    <div class="media-content-container">
        <div class="media-library__content">
            <x-filament::grid class="media-library__content__items" :default="$this->selectedMedia ? 1 : 2" 2xl="9" :xl="$this->selectedMedia ? 4 : 6"
                :lg="$this->selectedMedia ? 2 : 4" :md="$this->selectedMedia ? 2 : 3">
                @foreach ($mediaItems as $mediaItem)
                    <x-inspirecms-support::media-library.media-browser-item :mediaItem="$mediaItem" />
                @endforeach
            </x-filament::grid>
        </div>
        @if ($this->selectedMedia)
            <x-inspirecms-support::media-library.detail-info :media-item="$this->selectedMedia" :actions="$this->getActionsForAsset($this->selectedMedia)"/>
        @endif
    </div>

    <x-filament-actions::modals />
    
</div>
