@php
    $formKey = $this->getId() . '.forms.' . $this->getFormStatePathFor('uploadFileForm');
    $modelableConfig = $this->modelableConfig;
@endphp
<div class="media-library gap-3" x-data="{
    selectedMediaId: $wire.entangle('selectedMediaId').live,
    isSelectMultiple: $wire.entangle('isMultiple').live,
    isMediaSelected: function (mediaId) {
        if (this.isSelectMultiple) {
            return this.selectedMediaId.includes(mediaId);
        } else {
            return this.selectedMediaId === mediaId;
        }
    },
    selectMedia: function (mediaId, isFolder) {
        if (this.isSelectMultiple && ! isFolder) {
            if (this.selectedMediaId) {
                if (this.selectedMediaId.includes(mediaId)) {
                    this.selectedMediaId = this.selectedMediaId.filter(id => id !== mediaId);
                } else {
                    this.selectedMediaId = [...this.selectedMediaId, mediaId];
                }
            } else {
                this.selectedMediaId = [mediaId];
            }
        } else if (! this.isSelectMultiple){
            this.selectedMediaId = mediaId;
        }
    },
    init: function () {
        this.selectedMediaId = this.isSelectMultiple ? [] : null;
    },
}" 
    @if (!empty($modelableConfig))
        @php
            $modelable = array_key_first($modelableConfig);
            $model = array_values($modelableConfig)[0] ?? null;
        @endphp
        x-modelable="{{ $modelable }}" x-model="{{ $model }}"
    @endif
>
    <div class="pb-2 flex gap-2 justify-end">
        <x-filament::button 
            size="md" 
            wire:click="mountAction('createFolder')"
        >
            {{ trans('inspirecms-support::media-library.actions.create_folder.label') }}
        </x-filament::button>
    </div>

    <div class="form-container">
        <x-inspirecms-support::media-library.upload-form :livewireKey="$formKey">
            {{ $this->uploadFileForm }}
        </x-inspirecms-support::media-library.upload-form>
    </div>
    
    <div class="form-container">
        <x-inspirecms-support::media-library.filter-form :areFiltersOpen="$this->hasFilters()">
            {{ $this->filterForm }}
        </x-inspirecms-support::media-library.filter-form>
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
            <x-filament::grid class="media-library__content__items"
                :default="$this->selectedMedia ? 1 : 2"
                2xl="9"
                :xl="$this->selectedMedia ? 4 : 6"
                :lg="$this->selectedMedia ? 2 : 4"
                :md="$this->selectedMedia ? 2 : 3"
            >
                @foreach ($mediaItems as $mediaItem)
                    <x-inspirecms-support::media-library.media-browser-item :mediaItem="$mediaItem" class="media-library__content__items__item" />
                @endforeach
            </x-filament::grid>
        </div>
        @if ($this->selectedMedia)
            <x-inspirecms-support::media-library.detail-info :mediaItem="$this->selectedMedia" />
        @endif
        <x-filament-actions::modals />
    </div>
</div>