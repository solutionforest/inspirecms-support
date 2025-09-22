<?php

namespace SolutionForest\InspireCms\Support\Helpers;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Model;

class TreeNodeActionHelper
{
    /**
     * @param array $node
     * @param array<Action|ActionGroup> $livewireActions
     * @return array<Action|ActionGroup>
     */
    public static function getNodeActions(array $node, array $livewireActions, string | Model | null $model = null, bool $applyRecord = true, string $idName = 'id', string $actionName = '__visibleActions'): array
    {
        $actionNames = $node[$actionName] ?? [];

        if (empty($actionNames)) {
            return [];
        }

        $nodeId = $node[$idName] ?? null;
        if (! $nodeId && $applyRecord) {
            throw new \Exception('Node ID is missing');
        }

        /**
         * @var array<int,Action|ActionGroup>
         */
        $filteredActions = [];

        foreach ($livewireActions as $action) {
            if ($action instanceof Action) {
                if (in_array($action->getName(), $actionNames)) {

                    if ($model && is_string($model && is_a(Model::class, $model, true))) {
                        $action = $action->model($model);
                    }

                    if ($applyRecord) {
                        $action = $action
                            ->arguments(['node' => $node])
                            ->record($nodeId)
                            ->resolveRecordUsing(function ($arguments, $key, $model) {
                                if ($key instanceof Model) {
                                    return $key;
                                }
                                $recordKey = $arguments['nodeId'] ?? $key ?? null;
                                if (is_null($recordKey) || empty($recordKey)) {
                                    return null;
                                }

                                if (is_null($model) || empty($model)) {
                                    return null;
                                }
                                return $model::find($recordKey);
                            });
                            
                    }

                    $filteredActions[] = $action;
                }
            } elseif ($action instanceof ActionGroup) {

                // Check if any actions in the group are visible for this node
                $groupActions = static::getNodeActions($node, $action->getActions(), $applyRecord);
                if (!empty($groupActions)) {
                    $filteredActions[] = $action->actions($groupActions);
                }
            }
        }

        return $filteredActions;
    }
}
