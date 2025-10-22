@php
    use Filament\Forms\Components\TableSelect\Livewire\TableSelectLivewireComponent;

    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributes = $getExtraAttributes();
    $id = $getId();

    $filterTypes = $getFilterTypes();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        {{
            $attributes
                ->merge([
                    'id' => $id,
                ], escape: false)
                ->merge($extraAttributes, escape: false)
                ->merge([
                    'style' => 'min-height: 100px;',
                ])
        }}
    >
        @livewire('inspirecms-support::media-library', [
            'lazy' => true,
            'isDisabled' => $isDisabled(),
            'isModalPicker' => true,
            'filter' => [
                'type' => $filterTypes,
            ],
            'formConfig' => [
                'filter' => [
                    'disabled_columns' => !empty($filterTypes) ? ['type'] : [],
                ],
            ],
            'maxSelections' => $getMax(),
            $applyStateBindingModifiers('wire:model') => $getStatePath(),
        ], key($getLivewireKey()))
    </div>
</x-dynamic-component>