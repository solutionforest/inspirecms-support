@props(['livewireKey'])
<form 
    method="post"
    x-data="{ isProcessing: false }"
    x-on:submit="if (isProcessing) $event.preventDefault()"
    x-on:form-processing-started="isProcessing = true"
    x-on:form-processing-finished="isProcessing = false"
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
                x-bind:disabled="isProcessing == true"
                x-bind:class="{ 'opacity-70 cursor-wait': isProcessing }"
            >
                {{ trans('inspirecms-support::media-library.actions.upload.label') }}
            </x-filament::button>
        </div>
    </div>
</form>