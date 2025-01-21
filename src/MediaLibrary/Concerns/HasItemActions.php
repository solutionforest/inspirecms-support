<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Support\Exceptions\Cancel;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions;

trait HasItemActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    /**
     * @var array<string, Actions\Action | Actions\ActionGroup>
     */
    protected array $cachedFlatMediaItemActions = [];

    /**
     * @var array<Actions\Action | Actions\ActionGroup>
     */
    protected array $cachedMediaItemActions = [];

    public int | string | array | null $mountedMediaItemActionRecord = null;

    protected null | Model | Collection $cachedMountedMediaItemActionRecord = null;

    protected int | string | array | null $cachedMountedMediaItemActionRecordKey = null;

    public function bootedHasItemActions()
    {
        $this->cacheMediaItemActions();
    }

    /**
     * @param  Actions\Action | Actions\ActionGroup  $action
     */
    protected function configureMediaItemAction($action): void {}

    protected function getMediaItemActions(): array
    {
        return [];
    }

    public function getMountedAction(): ?Action
    {
        if (! count($this->mountedActions ?? [])) {
            return null;
        }

        $action = $this->getAction($this->mountedActions);

        if (($action instanceof Actions\Action || $action instanceof Actions\ActionGroup) && ($mountedRecord = $this->getMountedMediaItemActionRecord())) {
            if ($mountedRecord instanceof Model) {
                $action->record($mountedRecord);
            } elseif ($mountedRecord instanceof Collection) {
                $action->records($mountedRecord);
            }
        }

        return $action;
    }

    public function mountedMediaLibraryItemActionRecord(int | string | array | null $record): void
    {
        $this->mountedMediaItemActionRecord = $record;
    }

    /**
     * @param  string  $name
     * @param  null | string | array  $record
     * @return mixed
     */
    public function mountMediaLibraryItemAction($name, $record = null, array $arguments = [])
    {
        $this->mountedActions[] = $name;
        $this->mountedActionsArguments[] = $arguments;
        $this->mountedActionsData[] = [];

        if (count($this->mountedActions) === 1) {
            $this->mountedMediaLibraryItemActionRecord($record);
        }

        $action = $this->getMountedAction();

        if (! $action) {
            $this->unmountMediaItemAction();

            return null;
        }

        if (filled($record)) {

            if ($action instanceof Actions\ItemAction && ($action->getRecord() === null)) {
                $this->unmountMediaItemAction();

                return null;

            } elseif ($action instanceof Actions\ItemBulkAction && ($action->getRecords() === null)) {
                $this->unmountMediaItemAction();

                return null;

            } elseif (! ($action instanceof Actions\ItemAction || $action instanceof Actions\ItemBulkAction) && $action instanceof Actions\Action && ($action->getRecord() === null)) {
                $this->unmountMediaItemAction();

                return null;

            }
        }

        if ($action->isDisabled()) {
            $this->unmountMediaItemAction();

            return null;
        }

        $this->cacheMountedActionForm(mountedAction: $action);

        try {
            $hasForm = $this->mountedActionHasForm(mountedAction: $action);

            if ($hasForm) {
                $action->callBeforeFormFilled();
            }

            $action->mount([
                'form' => $this->getMountedActionForm(mountedAction: $action),
            ]);

            if ($hasForm) {
                $action->callAfterFormFilled();
            }
        } catch (Halt $exception) {
            return null;
        } catch (Cancel $exception) {
            $this->unmountMediaItemAction(shouldCancelParentActions: false);

            return null;
        }

        if (! $this->mountedActionShouldOpenModal(mountedAction: $action)) {
            return $this->callMountedAction();
        }

        $this->resetErrorBag();

        $this->openActionModal();

        return null;
    }

    protected function resetMountedMediaItemActionProperties(): void
    {
        $this->resetMountedActionProperties();
        $this->mountedMediaItemActionRecord = null;
    }

    public function unmountMediaItemAction(bool $shouldCancelParentActions = true, bool $shouldCloseModal = true): void
    {
        $action = $this->getMountedAction();

        if (! ($shouldCancelParentActions && $action)) {
            $this->popMountedAction();
        } elseif ($action->shouldCancelAllParentActions()) {
            $this->resetMountedMediaItemActionProperties();
        } else {
            $parentActionToCancelTo = $action->getParentActionToCancelTo();

            while (true) {
                $recentlyClosedParentAction = $this->popMountedAction();

                if (
                    blank($parentActionToCancelTo) ||
                    ($recentlyClosedParentAction === $parentActionToCancelTo)
                ) {
                    break;
                }
            }
        }

        if (! count($this->mountedActions)) {
            if ($shouldCloseModal) {
                $this->closeActionModal();
            }

            $action?->clearRecordAfter();

            // Setting these to `null` creates a bug where the properties are
            // actually set to `'null'` strings and remain in the URL.
            $this->defaultAction = [];
            $this->defaultActionArguments = [];

            return;
        }

        $this->cacheMountedActionForm();

        $this->resetErrorBag();

        $this->openActionModal();
    }

    public function getMountedMediaItemActionRecordKey(): int | string | array | null
    {
        return $this->mountedMediaItemActionRecord;
    }

    public function getMountedMediaItemActionRecord(): null | Model | Collection
    {
        $recordKey = $this->getMountedMediaItemActionRecordKey();

        if ($this->cachedMountedMediaItemActionRecord && ($this->cachedMountedMediaItemActionRecordKey === $recordKey)) {
            return $this->cachedMountedMediaItemActionRecord;
        }

        $this->cachedMountedMediaItemActionRecordKey = $recordKey;

        if (is_null($recordKey)) {
            return $this->cachedMountedMediaItemActionRecord = null;
        }

        return $this->cachedMountedMediaItemActionRecord = is_array($recordKey)
            ? $this->resolveAssetRecords($recordKey)
            : $this->resolveAssetRecord($recordKey);
    }

    protected function cacheMediaItemActions(): void
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

                return;
            }

            if (! $action instanceof Action) {
                throw new InvalidArgumentException('The actions must be an instance of ' . Action::class . ', or ' . ActionGroup::class . '.');
            }

            $action = $this->cacheAction($action);
            $this->cacheMediaItemAction($action);
            $this->cachedMediaItemActions[] = $action;
        }
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
}
