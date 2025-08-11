<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Exceptions\ActionNotResolvableException;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\ItemBulkAction;

trait HasItemActions
{
    use InteractsWithActions {
        resolveAction as baseResolveAction;
    }
    use InteractsWithForms;

    /**
     * @var array<string, Actions\Action>
     */
    protected array $cachedFlatMediaItemActions = [];

    /**
     * @var array<Actions\Action | Actions\ActionGroup>
     */
    protected array $cachedMediaItemActions = [];

    public function bootedHasItemActions()
    {
        $this->cacheHasItemActions();
    }

    public function cacheHasItemActions()
    {
        /** @var array<string, Actions\Action | Actions\ActionGroup> */
        $actions = Action::configureUsing(
            Closure::fromCallable([$this, 'configureMediaItemAction']),
            fn (): array => $this->getMediaItemActions(),
        );

        foreach ($actions as $action) {

            if ($action instanceof ActionGroup || $action instanceof Actions\ActionGroup) {
                $action->livewire($this);

                /** @var array<string, Action> $flatActions */
                $flatActions = $action->getFlatActions();

                $this->mergeCachedActions($flatActions);
                foreach ($flatActions as $flatAction) {
                    $this->cacheMediaItemAction($flatAction);
                }

                $this->cachedMediaItemActions[] = $action;

                continue;
            }

            if (! $action instanceof Action) {
                throw new InvalidArgumentException('The actions must be an instance of ' . Action::class . ', or ' . ActionGroup::class . '.');
            }

            $action = $this->cacheAction($action);
            if (! isset($this->cachedFlatMediaItemActions[$action->getName()])) {
                $this->cachedMediaItemActions[] = $action;
            }
            $this->cacheMediaItemAction($action);
        }
    }

    /**
     * @param  Actions\Action | Actions\ActionGroup  $action
     */
    protected function configureMediaItemAction($action): void {}

    protected function getMediaItemActions(): array
    {
        return [];
    }

    /**
     * @param  string  $name
     * @param  null | string | array  $record
     * @return mixed
     */
    public function mountMediaLibraryItemAction($name, $record = null, array $arguments = [])
    {
        return $this->mountAction($name, $arguments, context: [
            'mediaLibrary' => true,
            'recordKey' => $record,
        ]);
    }

    public function unmountMediaItemAction(bool $shouldCancelParentActions = true, bool $shouldCloseModal = true): void
    {
        $this->unmountAction(
            $shouldCancelParentActions,
            $shouldCloseModal,
        );
    }

    protected function cacheMediaItemAction($action)
    {
        $action->livewire($this);
        $this->cachedFlatMediaItemActions[$action->getName()] = $action;
    }

    public function getCachedMediaItemActions(): array
    {
        return $this->cachedMediaItemActions;
    }

    /**
     * @param  array<string, mixed>  $action
     * @param  array<Action>  $parentActions
     */
    protected function resolveAction(array $action, array $parentActions): ?Action
    {
        if (($action['context']['mediaLibrary'] ?? null)) {
            return $this->resolveMediaLibraryAction($action, $parentActions);
        }

        return $this->baseResolveAction($action, $parentActions);
    }

    /**
     * @param  array<string, mixed>  $action
     * @param  array<Action>  $parentActions
     */
    protected function resolveMediaLibraryAction(array $action, array $parentActions): ?Action
    {
        if (! in_array(WithMediaAssets::class, class_uses_recursive($this))) {
            throw new ActionNotResolvableException('The action [' . $action['name'] . '] cannot be resolved on the current model, because it does not use the ' . WithMediaAssets::class . ' trait.');
        }

        $resolvedAction = null;

        if (count($parentActions)) {
            $parentAction = Arr::last($parentActions);
            $resolvedAction = $parentAction->getModalAction($action['name']) ?? throw new ActionNotResolvableException("Action [{$action['name']}] was not found for action [{$parentAction->getName()}].");
        } else {
            $resolvedAction = $this->cachedFlatMediaItemActions[$action['name']] ?? throw new ActionNotResolvableException("Action [{$action['name']}] not found on media library.");
        }

        if (filled($action['context']['recordKey'] ?? null)) {

            $isBulk = is_array($action['context']['recordKey']);

            $targetAction = $resolvedAction->getRootGroup() ?? $resolvedAction;

            if ($isBulk && $targetAction instanceof ItemBulkAction) {
                // Skip, resolveAssetRecords is handled in the action itself.
            } else {
                $targetAction->record($this->resolveAssetRecord($action['context']['recordKey']));
            }

        }

        return $resolvedAction;
    }
}
