
<div class="form-container-content sort-form-container-content items-center" x-data="{
    isCollapsed: @js($isCollapsed),
}" x-cloak>
    <x-filament::icon-button
        x-on:click="isCollapsed = ! isCollapsed"
        icon="heroicon-m-arrows-up-down"
        color="gray"
        label="Toggle sort form"
    />
    <form 
        method="post"
        id="sortForm"
        x-show="!isCollapsed"
    >
        {{ $slot}}
    </form>
</div>