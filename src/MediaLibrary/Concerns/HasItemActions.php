<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Exceptions\ActionNotResolvableException;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\ItemBulkAction;

trait HasItemActions
{
    use InteractsWithActions {
        resolveAction as baseResolveAction;
    }
    use InteractsWithSchemas;

    /**
     * @var array<string, Actions\Action>
     */
    protected array $cachedMediaItemActions = [];

    public function cacheHasItemActions()
    {
        /** @var array<string, Actions\Action | Actions\ActionGroup> */
        $actions = Action::configureUsing(
            Closure::fromCallable([$this, 'configureMediaItemAction']),
            fn (): array => $this->getMediaItemActions(),
        );

        foreach ($actions as $action) {

            if ($action instanceof ActionGroup) {
                foreach ($action->getFlatActions() as $subAction) {
                    $this->cacheMediaItemAction($subAction);
                }
            } elseif ($action instanceof Action) {
                $this->cacheMediaItemAction($action);
            }
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

    public function getVisibleMediaItemActions(): array
    {
        return array_filter(
            $this->getMediaItemActions(),
            fn (Action | ActionGroup $action): bool => $action->isVisible(),
        );
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

    public function cacheMediaItemAction(Action $action): Action
    {
        $action->livewire($this);

        return $this->cachedMediaItemActions[$action->getName()] = $action;
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
            $resolvedAction = $this->cachedMediaItemActions[$action['name']] ?? throw new ActionNotResolvableException("Action [{$action['name']}] not found on media library.");
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
