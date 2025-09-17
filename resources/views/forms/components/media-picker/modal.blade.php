
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
    class="media-library-browser-modal-content"
    display-classes="block"
    x-init="() => {
        this.selectedMediaAssets = [];
        this.formKey = false;
    }"
    x-on:x-media-picker-modal-setup.window="
        if ($event?.detail?.modalId == '{{ $modalId }}') {

            this.selectedMediaAssets = $event?.detail?.selected ?? [];
            this.formKey = $event?.detail?.key ?? null;

            // Update setting on livewire component
            $dispatch('media-library:modal-setup', { 
                config: $event?.detail?.config ?? [],
            });

            if ($event.detail?.openModal ?? false) {
                open();
            }
        }
    "
>
    <x-slot name="heading">
        {{ __('inspirecms-support::media-library.buttons.select.heading') }}
    </x-slot>

    <livewire:inspirecms-support::media-library
        lazy
        :isModalPicker="true"
    />

    <x-slot name="footerActions">
        <x-filament::button x-on:click="
            $dispatch(
                'update-media-picker-selection', 
                { 
                    id: '{{ $modalId }}', 
                    key: this.formKey, 
                    data: this.selectedMediaAssets 
                }
            );
            close();
        ">
            {{ __('inspirecms-support::media-library.buttons.select.label') }}
        </x-filament::button>
        <x-filament::button color="gray" x-on:click="close()">
            {{ __('inspirecms-support::media-library.buttons.cancel.label') }}
        </x-filament::button>
    </x-slot>

</x-filament::modal>