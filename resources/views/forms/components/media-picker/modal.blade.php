
@php
    $modelableConfig = ['selectedMediaId' => 'selected'];
    $modalId = 'media-library-picker-modal';
    $livewireId = 'media-library-picker-modal_'  . rand();
@endphp

<x-filament::modal 
    id="{{ $modalId }}"
    sticky-header
    sticky-footer
    footer-actions-alignment="end"
    width="screen"
    class="media-library-browser-modal"
    x-data="{ 
        selected: [], 
        formStatePath: '', 
        modalInitialized: false,
        closeMediaPickerModal: function(save = true) {
            console.log('onCloseMediaPickerModal', this.selected);
            if (save) {
                this.$dispatch('close-modal', { id: '{{ $modalId }}', save: true, statePath: this.formStatePath, data: { selected: this.selected } });
            } else {
                this.$dispatch('close-modal', { id: '{{ $modalId }}', save: false, statePath: this.formStatePath });
            }
        },
        onMediaPickerModalSetup: function($event) {
            this.selected = $event.detail.selected ?? [];
            this.formStatePath = $event.detail.statePath ?? '';
            this.modalInitialized = false;

            this.$dispatch('media-picker-modal:init', { config: $event.detail?.config ?? [] });
        },
        onMediaPickerModalSetupComplete: function($event) {
            this.modalInitialized = true;
        },
    }"
    x-on:media-picker-setup.window="onMediaPickerModalSetup($event)"
    x-on:media-picker-modal-setup-complete="onMediaPickerModalSetupComplete($event)"
>
    <x-slot name="heading">
        {{ __('inspirecms-support::actions.select.modal.heading') }}
    </x-slot>

    <div x-show="!modalInitialized">
        Loading...
    </div>
    <div x-show="modalInitialized">
        <livewire:inspirecms-support::media-library
            is-modal-picker="true"
        />
    </div>

    <x-slot name="footerActions">
        <x-filament::button x-on:click="closeMediaPickerModal(true)">
            {{ __('inspirecms-support::actions.select.label') }}
        </x-filament::button>
        <x-filament::button color="gray" x-on:click="closeMediaPickerModal(false)">
            {{ __('inspirecms-support::actions.cancel.label') }}
        </x-filament::button>
    </x-slot>

</x-filament::modal>