
<div class="form-container-content filter-form-container-content" x-data="{
    isCollapsed: @js($isCollapsed),
}" x-cloak>
    <x-filament::icon-button
        x-on:click="isCollapsed = ! isCollapsed"
        icon="heroicon-m-funnel"
        color="gray"
        label="Toggle filter form"
    />
    <form 
        method="post"
        id="filterForm"
        x-show="!isCollapsed"
    >
        {{ $slot}}
    </form>
</div>