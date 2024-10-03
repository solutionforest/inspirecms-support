<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer\Concerns;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Arr;

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
     * @param  array<Action>  $actions
     */
    public function pushActions(array $actions): static
    {
        foreach (Arr::wrap($actions) as $action) {

            if ($action instanceof Action) {
                $action->defaultSize(ActionSize::Small);
                $action->defaultView($action::LINK_VIEW);

                $this->cacheAction($action);
            } else {
                throw new \InvalidArgumentException('Table actions must be an instance of ' . Action::class . ' or ' . ActionGroup::class . '.');
            }

            $this->actions[] = $action;
        }

        return $this;
    }

    /**
     * @return array<Action | ActionGroup>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param  string | array<string>  $name
     */
    public function getAction(string | array $name): ?Action
    {
        if (is_string($name) && str($name)->contains('.')) {
            $name = explode('.', $name);
        }

        if (is_array($name)) {
            $firstName = array_shift($name);
            $modalActionNames = $name;

            $name = $firstName;
        }

        $mountedRecord = $this->getLivewire()->getMountedTableActionRecord();

        $action = $this->getFlatActions()[$name] ?? null;

        if (! $action) {
            return null;
        }

        return $this->getMountableModalActionFromAction(
            $action->record($mountedRecord),
            modalActionNames: $modalActionNames ?? [],
            mountedRecord: $mountedRecord,
        );
    }

    /**
     * @return array<string, Action>
     */
    public function getFlatActions(): array
    {
        return $this->flatActions;
    }

    public function hasAction(string $name): bool
    {
        return array_key_exists($name, $this->getFlatActions());
    }

    protected function cacheAction(Action $action, bool $shouldOverwriteExistingAction = true): void
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
    protected function getMountableModalActionFromAction(Action $action, array $modalActionNames, ?Model $mountedRecord = null): ?Action
    {
        $arguments = $this->getLivewire()->mountedTableActionsArguments ?? [];

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

            if ($action instanceof Action) {
                $action->record($mountedRecord);
            }

            if (
                (($actionArguments = array_shift($arguments)) !== null) &&
                (! $action->hasArguments())
            ) {
                $action->arguments($actionArguments);
            }
        }

        if (! $action instanceof Action) {
            return null;
        }

        return $action;
    }
}
