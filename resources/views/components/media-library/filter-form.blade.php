@php
    use Illuminate\Support\Arr;
@endphp
<div {{ $attributes->merge([
        'class' => Arr::toCssClasses([
            'form-container-content filter-form-container-content gap-2',
        ]),
    ]) }} 
    x-data="{
        isCollapsed: @js($isCollapsed),
    }" 
    x-bind:class="{ 'min-h-20': ! isCollapsed }"
    x-cloak
>
    <x-filament::icon-button
        x-on:click="isCollapsed = ! isCollapsed"
        icon="heroicon-m-funnel"
        color="gray"
        label="Toggle filter form"
        class="py-1"
    />
    <form 
        method="post"
        id="filterForm"
        x-show="!isCollapsed"
        class="w-full"
    >
        {{ $slot}}
    </form>
</div>