@props(['mediaItem', 'actions' => []])
@use('Filament\Actions\Action')
@use('Filament\Actions\ActionGroup')
@use('Illuminate\Database\Eloquent\Model')
@use('SolutionForest\InspireCms\Support\MediaLibrary\Actions\Action', 'MediaLibraryAction')
@use('SolutionForest\InspireCms\Support\MediaLibrary\Actions\ActionGroup', 'MediaLibraryActionGroup')
@use('SolutionForest\InspireCms\Support\MediaLibrary\Actions\ItemBulkAction', 'MediaLibraryItemBulkAction')

@php
    $mediaAssetsActions = array_filter(
        array_map(
            function ($action) use ($mediaItem) {
                // Single record action
                if ($mediaItem instanceof Model && ($action instanceof Action || $action instanceof MediaLibraryActionGroup)) {
                    $action = $action->record($mediaItem);
                } 
                // Bulk action
                elseif ($action instanceof MediaLibraryItemBulkAction || $action instanceof MediaLibraryActionGroup) {
                    $action = $action->records($mediaItem);
                }
                return $action;
            },
            $actions,
        ),
        function ($action): bool {
            return $action->isVisible();
        },
    );
    $livewireKey = $mediaItem instanceof Model ? "mediaasset-{$mediaItem->getKey()}-actions" : 'mediaasset-actions';
@endphp

@if (count($mediaAssetsActions) > 0)
    <x-filament::dropdown wire:key="{{ $livewireKey }}" placement="right-start">
        <x-slot name="trigger">
            <x-filament::icon-button icon="heroicon-m-ellipsis-vertical" color="gray">
                More actions
            </x-filament::icon-button>
        </x-slot>
        <div class="fi-dropdown-list p-1">
            @foreach ($mediaAssetsActions as $mediaAssetsAction)
                {{ $mediaAssetsAction->grouped() }}
            @endforeach
        </div>
    </x-filament::dropdown>
@else
    <div></div>
@endif