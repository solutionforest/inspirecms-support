@props([
    'alpineNodeIdVariable',
    'actions' => [],
])

@php
    $actions = collect($actions)
        ->map(fn ($action) => $action->recursiveTreeNodeAlpineId($alpineNodeIdVariable))
        ->filter(fn ($action) => $action->isVisible())
        ->all();
@endphp

<x-filament::actions :actions="$actions" />