<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer\Concerns;

use Filament\Actions\Action;
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
                throw new \InvalidArgumentException('The actions must be an instance of ' . Action::class . '.');
            }

            $this->actions[] = $action;
        }

        return $this;
    }

    /**
     * @return array<Action>
     */
    public function getActions(): array
    {
        return $this->actions;
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
}
