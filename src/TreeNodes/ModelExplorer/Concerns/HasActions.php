<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer\Concerns;

use Filament\Actions\Action;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action as TreeNodeAction;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\ActionGroup;

trait HasActions
{
    /**
     * @var array<Action>
     */
    protected array $actions = [];

    /**
     * @var array<string, Action>
     */
    protected array $flatActions = [];

    /**
     * @param  array<Action>  $actions
     */
    public function actions(array $actions): static
    {
        $this->actions = [];
        $this->pushActions($actions);

        return $this;
    }

    /**
     * @param  array<Action|TreeNodeAction|ActionGroup>  $actions
     */
    public function pushActions(array $actions): static
    {
        foreach (Arr::wrap($actions) as $action) {

            if ($action instanceof ActionGroup || $action instanceof TreeNodeAction) {
                $action->treeNode($this);
            }

            if ($action instanceof ActionGroup) {

                /** @var array<string, Action> $flatActions */
                $flatActions = $action->getFlatActions();

                if (! $action->getDropdownPlacement()) {
                    $action->dropdownPlacement('bottom-end');
                }

                $this->mergeCachedFlatActions($flatActions);

            } else if ($action instanceof Action) {
                $action->defaultSize(ActionSize::Small);
                $action->defaultView($action::LINK_VIEW);

                $this->cacheAction($action);
            } else {
                throw new \InvalidArgumentException('The actions must be an instance of ' . Action::class . ' or ' . ActionGroup::class . '.');
            }

            $this->actions[] = $action;
        }

        return $this;
    }

    /**
     * @return array<Action|TreeNodeAction|ActionGroup>
     */
    public function getVisibleActionsForItem(array $item = []): array
    {
        $actions = [];

        foreach ($this->actions as $action) {

            if ($action instanceof ActionGroup || $action instanceof TreeNodeAction) {
                $action->itemKey($this->getNodeItemKey($item));
            } 

            if ($action instanceof Action) {
                $action->arguments($this->getNodeItemArguments($item));
            }

            if (! $action->isVisible()) {
                continue;
            }

            $actions[] = $action;
        }

        return $actions;
    }

    /**
     * @param  string | array<string>  $name
     */
    public function getAction(string | array $name): null | Action | TreeNodeAction
    {
        if (is_string($name) && str($name)->contains('.')) {
            $name = explode('.', $name);
        }

        if (is_array($name)) {
            $firstName = array_shift($name);
            $modalActionNames = $name;

            $name = $firstName;
        }

        $mountedItemKey = $this->getLivewire()->getMountedTreeNodeItemActionRecord();

        $action = $this->getFlatActions()[$name] ?? null;

        if (! $action) {
            return null;
        }

        return $this->getMountableModalActionFromAction(
            ($action instanceof TreeNodeAction) ? $action->itemKey($mountedItemKey) : $action,
            modalActionNames: $modalActionNames ?? [],
            mountedItemKey: $mountedItemKey,
        );
    }

    /**
     * @return array<string, Action|TreeNodeAction>
     */
    public function getFlatActions(): array
    {
        return $this->flatActions;
    }

    public function hasAction(string $name): bool
    {
        return array_key_exists($name, $this->getFlatActions());
    }

    protected function cacheAction(Action|TreeNodeAction $action, bool $shouldOverwriteExistingAction = true): void
    {
        if ($shouldOverwriteExistingAction) {
            $this->flatActions[$action->getName()] = $action;
        } else {
            $this->flatActions[$action->getName()] ??= $action;
        }
    }

    /**
     * @param  array<string, Action>  $actions
     */
    protected function mergeCachedFlatActions(array $actions, bool $shouldOverwriteExistingActions = true): void
    {
        if ($shouldOverwriteExistingActions) {
            $this->flatActions = [
                ...$this->flatActions,
                ...$actions,
            ];
        } else {
            $this->flatActions = [
                ...$actions,
                ...$this->flatActions,
            ];
        }
    }

    /**
     * @param  array<string>  $modalActionNames
     */
    protected function getMountableModalActionFromAction(Action|TreeNodeAction $action, array $modalActionNames, null|string|int $mountedItemKey = null): null | Action | TreeNodeAction
    {
        $arguments = $this->getLivewire()->mountedTreeNodeItemActionsArguments ?? [];

        if (
            (($actionArguments = array_shift($arguments)) !== null) &&
            (! $action->hasArguments())
        ) {
            $action->arguments($actionArguments);
        }

        foreach ($modalActionNames as $modalActionName) {

            $action = $action->getMountableModalAction($modalActionName);


            if (! $action) {
                return null;
            }

            if ($action instanceof TreeNodeAction) {
                $action->itemKey($mountedItemKey);
            }

            if (
                (($actionArguments = array_shift($arguments)) !== null) &&
                (! $action->hasArguments())
            ) {
                $action->arguments($actionArguments);
            }
        }

        if (! ($action instanceof Action || $action instanceof TreeNodeAction)) {
            return null;
        }

        return $action;
    }
}
