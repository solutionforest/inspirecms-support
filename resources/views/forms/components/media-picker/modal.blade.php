
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
        this.selected = [];
        this.formStatePath = false;
        this.modalInitialized = false;
    }"
    x-on:media-picker-setup.window="(event) => {

        this.selected = event.detail.selected ?? [];
        this.formStatePath = event.detail.statePath ?? '';
        this.modalInitialized = false;

        $dispatch('media-picker-modal:init', { config: event.detail?.config ?? [] });
    }"
    x-on:media-picker-modal-setup-complete="() => {
        this.modalInitialized = true;
    }"
>
    <x-slot name="heading">
        {{ __('inspirecms-support::buttons.select.heading') }}
    </x-slot>

    <livewire:inspirecms-support::media-library
        is-modal-picker="true"
    />

    <x-slot name="footerActions">
        <x-filament::button x-on:click="$dispatch('close-modal', { id: '{{ $modalId }}', save: true, statePath: this.formStatePath, data: { selected: this.selected } })">
            {{ __('inspirecms-support::buttons.select.label') }}
        </x-filament::button>
        <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: '{{ $modalId }}', save: false, statePath: this.formStatePath })">
            {{ __('inspirecms-support::buttons.cancel.label') }}
        </x-filament::button>
    </x-slot>

</x-filament::modal>