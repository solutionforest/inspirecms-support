<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

/**
 * @method \Filament\Actions\Action cacheAction(\Filament\Actions\Action $action)
 * @method void mergeCachedActions(array $actions)
 */
trait InteractsWithHeaderActions
{
    protected function cacheInteractsWithHeaderActions(): void
    {
        /** @var array<string, Action | ActionGroup> */
        $actions = Action::configureUsing(
            Closure::fromCallable([$this, 'configureAction']),
            fn (): array => $this->getHeaderActions(),
        );

        foreach ($actions as $action) {

            if ($action instanceof ActionGroup) {
                $this->mergeCachedActions($action->getFlatActions());
            } elseif ($action instanceof Action) {
                $this->cacheAction($action);
            }
        }
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return $this->getActions();
    }

    public function getVisibleHeaderActions(): array
    {
        return collect($this->getHeaderActions())
            ->filter(fn (Action | ActionGroup $action) => $action->isVisible())
            ->all();
    }
}
