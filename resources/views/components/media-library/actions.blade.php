@props(['mediaItem', 'actions' => []])

@php
    use Illuminate\Database\Eloquent\Model;
    use SolutionForest\InspireCms\Support\MediaLibrary\Actions;

    $actions = array_filter(
        array_map(
            function ($action) use ($mediaItem) {
                // Single record action
                if ($mediaItem instanceof Model && ($action instanceof \Filament\Actions\Action || $action instanceof Actions\ActionGroup)) {
                    $action = $action->record($mediaItem);
                } 
                // Bulk action
                elseif ($action instanceof Actions\ItemBulkAction || $action instanceof Actions\ActionGroup) {
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
@endphp

@if (count($actions) > 0)
    <x-filament-actions::group
        :actions="$actions"
        color="gray"
    />
@else
    <div></div>
@endif