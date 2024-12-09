@php
    use Illuminate\Support\Arr;
@endphp
<div {{ $attributes->merge([
        'class' => Arr::toCssClasses([
            'form-container-content sort-form-container-content gap-2',
            'lg:!flex-row-reverse'
        ]),
    ]) }} 
    x-data="{
        isCollapsed: @js($isCollapsed),
    }" 
    x-cloak
>
    <x-filament::icon-button
        x-on:click="isCollapsed = ! isCollapsed"
        icon="heroicon-m-arrows-up-down"
        color="gray"
        label="Toggle sort form"
        class="py-1"
    />
    <form 
        method="post"
        id="sortForm"
        x-show="!isCollapsed"
    >
        {{ $slot}}
    </form>
</div>