
<div x-data="{
    areFiltersOpen: @js($areFiltersOpen),
}" x-cloak class="filter-form-container">
    <x-filament::icon-button
        x-on:click="areFiltersOpen = ! areFiltersOpen"
        class="ms-auto"
        icon="heroicon-m-funnel"
        color="gray"
    />
    <form 
        method="post"
        id="filterForm"
        x-show="areFiltersOpen"
    >
        {{ $slot}}
    </form>
</div>