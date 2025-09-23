<?php

namespace SolutionForest\InspireCms\Support\Helpers;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Model;

class TreeNodeActionHelper
{
    /**
     * @param array $node
     * @param array<Action|ActionGroup> $livewireActions
     * @param Closure(Model|string|int|null):Model|null $resolveRecordUsing
     * @param null | Model | string $model
     * @param ?\Livewire\Component $livewire
     * @return array<Action|ActionGroup>
     */
    public static function getNodeActions(array $node, array $livewireActions, ?Closure $resolveRecordUsing = null, string | Model | null $model = null, ?\Livewire\Component $livewire = null, string $idName = 'id', string $actionName = '__visibleActions'): array
    {
        $actionNames = $node[$actionName] ?? [];

        if (empty($actionNames)) {
            return [];
        }

        $nodeId = $node[$idName] ?? null;
        if (! $nodeId) {
            throw new \Exception('Node ID is missing');
        }

        /**
         * @var array<int,Action|ActionGroup>
         */
        $filteredActions = [];

        foreach ($livewireActions as $action) {
            if ($action instanceof Action) {
                if (in_array($action->getName(), $actionNames)) {

                    $action = $action->arguments(['node' => $node]);

                    if ($model && is_string($model && is_a(Model::class, $model, true))) {
                        $action = $action->model($model);
                    }

                    if ($resolveRecordUsing) {
                        $action = $action
                            ->record($nodeId)
                            ->resolveRecordUsing($resolveRecordUsing);
                            
                    }

                    if ($livewire) {
                        $action = $action->livewire($livewire);
                    }

                    $filteredActions[] = $action;
                }
            } elseif ($action instanceof ActionGroup) {

                // Check if any actions in the group are visible for this node
                $groupActions = static::getNodeActions(
                    node: $node,
                    livewireActions: $action->getActions(),
                    resolveRecordUsing: $resolveRecordUsing,
                    model: $model,
                    idName: $idName,
                    actionName: $actionName,
                );
                if (!empty($groupActions)) {
                    $filteredActions[] = $action->actions($groupActions);
                }
            }
        }

        return $filteredActions;
    }
}
