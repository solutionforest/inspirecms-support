@props(['livewireKey', 'isCollapsed' => false])

<div class="form-container-content" x-data="{
    isCollapsed: @js($isCollapsed),
}" x-cloak>

    <x-filament::icon-button
        x-on:click="isCollapsed = ! isCollapsed"
        icon="heroicon-o-cloud-arrow-up"
        color="primary"
        size="lg"
        label="Toggle upload form"
    />
    <div class="flex-1" x-show="!isCollapsed">
        <form 
            method="post"
            x-data="{ 
                isProcessing: false
            }"
            
            x-on:submit="if (isProcessing) $event.preventDefault()"
            x-on:form-processing-started="isProcessing = true"
            x-on:form-processing-finished="isProcessing = false"
            id="uploadFileForm"
            wire:key="{{$livewireKey}}"
            wire:submit="saveUploadFile"
        >
            {{ $slot }}
            
            <div class="media-library__form__actions pt-2">
                <div class="fi-ac gap-3 flex flex-wrap items-center flex-row-reverse">
                    <x-filament::button 
                        class="media-library__form__actions__button"
                        size="md" 
                        type="submit"
                        form="uploadFileForm"
                    >
                        {{ trans('inspirecms-support::media-library.actions.upload.label') }}
                    </x-filament::button>
                </div>
            </div>
        </form>
    </div>
</div>