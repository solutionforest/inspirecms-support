<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Support\Exceptions\Cancel;
use Filament\Support\Exceptions\Halt;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action as TreeNodeAction;
use Throwable;

use function Livewire\store;

/**
 * @property Form $mountedTreeNodeItemActionForm
 */
trait HasTreeNodeItemActions
{
    /**
     * @var array<string> | null
     */
    public ?array $mountedTreeNodeItemActions = [];

    /**
     * @var array<string, array<string, mixed>> | null
     */
    public ?array $mountedTreeNodeItemActionsArguments = [];

    /**
     * @var array<string, array<string, mixed>> | null
     */
    public ?array $mountedTreeNodeItemActionsData = [];

    public int | string | null $mountedTreeNodeItemActionRecord = null;

    /**
     * @var mixed
     */
    #[Url(as: 'treeNodeItemAction')]
    public $defaultTreeNodeItemAction = null;

    /**
     * @var mixed
     */
    #[Url(as: 'treeNodeItemActionArguments')]
    public $defaultTreeNodeItemActionArguments = null;

    /**
     * @var mixed
     */
    #[Url(as: 'treeNodeItemActionRecord')]
    public $defaultTreeNodeItemActionRecord = null;

    protected function configureSelectedModelItemFormAction(Action | TreeNodeAction $action): void {}

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function callMountedTreeNodeItemAction(array $arguments = []): mixed
    {
        $action = $this->getMountedTreeNodeItemAction();

        if (! $action) {
            return null;
        }

        if (filled($this->mountedTreeNodeItemActionRecord) && ($action->getRecord() === null)) {
            return null;
        }

        if ($action->isDisabled()) {
            return null;
        }

        $action->mergeArguments($arguments);

        $form = $this->getMountedTreeNodeItemActionForm(mountedAction: $action);

        $result = null;

        $originallyMountedActions = $this->mountedTreeNodeItemActions;

        try {
            $action->beginDatabaseTransaction();

            if ($this->mountedTreeNodeItemActionHasForm(mountedAction: $action)) {
                $action->callBeforeFormValidated();

                $form->getState(afterValidate: function (array $state) use ($action) {
                    $action->callAfterFormValidated();

                    $action->formData($state);

                    $action->callBefore();
                });
            } else {
                $action->callBefore();
            }

            $result = $action->call([
                'form' => $form,
            ]);

            $result = $action->callAfter() ?? $result;

            $action->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $action->rollBackDatabaseTransaction() :
                $action->commitDatabaseTransaction();

            return null;
        } catch (Cancel $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $action->rollBackDatabaseTransaction() :
                $action->commitDatabaseTransaction();
        } catch (ValidationException $exception) {
            $action->rollBackDatabaseTransaction();

            if (! $this->mountedTreeNodeItemActionShouldOpenModal(mountedAction: $action)) {
                $action->resetArguments();
                $action->resetFormData();

                $this->unmountTreeNodeItemAction();
            }

            throw $exception;
        } catch (Throwable $exception) {
            $action->rollBackDatabaseTransaction();

            throw $exception;
        }

        if (store($this)->has('redirect')) {
            return $result;
        }

        $action->resetArguments();
        $action->resetFormData();

        // If the action was replaced while it was being called,
        // we don't want to unmount it.
        if ($originallyMountedActions !== $this->mountedTreeNodeItemActions) {
            return null;
        }

        $this->unmountTreeNodeItemAction();

        return $result;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function mountTreeNodeItemAction(string $name, int | string | null $itemKey, array $arguments = []): mixed
    {
        $this->mountedTreeNodeItemActions[] = $name;
        $this->mountedTreeNodeItemActionsArguments[] = $arguments;
        $this->mountedTreeNodeItemActionsData[] = [];

        if (count($this->mountedTreeNodeItemActions) === 1) {
            $this->mountedTreeNodeItemActionRecord($itemKey);
        }

        $action = $this->getMountedTreeNodeItemAction();

        // Unmount the action if it is not found.
        if (! $action) {
            $this->unmountTreeNodeItemAction();

            return null;
        }

        // Unmount the action if the item key is not set.
        if (filled($itemKey) &&
            (
                ($action instanceof TreeNodeAction && blank($action->getItemKey())) ||
                ($action instanceof Action && ! ($action instanceof TreeNodeAction) && $action->getRecord() === null)
            )
        ) {
            $this->unmountTreeNodeItemAction();

            return null;
        }

        // Unmount the action if it is disabled.
        if ($action->isDisabled()) {
            $this->unmountTreeNodeItemAction();

            return null;
        }

        $this->cacheMountedTreeNodeItemActionForm(mountedAction: $action);

        try {
            $hasForm = $this->mountedTreeNodeItemActionHasForm(mountedAction: $action);

            if ($hasForm) {
                $action->callBeforeFormFilled();
            }

            $action->mount([
                'form' => $this->getMountedTreeNodeItemActionForm(mountedAction: $action),
            ]);

            if ($hasForm) {
                $action->callAfterFormFilled();
            }
        } catch (Halt $exception) {
            return null;
        } catch (Cancel $exception) {
            $this->unmountTreeNodeItemAction(shouldCancelParentActions: false);

            return null;
        }

        if (! $this->mountedTreeNodeItemActionShouldOpenModal(mountedAction: $action)) {
            return $this->callMountedTreeNodeItemAction();
        }

        $this->resetErrorBag();

        $this->openTreeNodeItemActionModal();

        return null;
    }

    public function unmountTreeNodeItemAction(bool $shouldCancelParentActions = true, bool $shouldCloseModal = true): void
    {
        $action = $this->getMountedTreeNodeItemAction();

        if (! ($shouldCancelParentActions && $action)) {
            $this->popMountedTreeNodeItemAction();
        } elseif ($action->shouldCancelAllParentActions()) {
            $this->resetMountedTreeNodeItemActionProperties();
        } else {
            $parentActionToCancelTo = $action->getParentActionToCancelTo();

            while (true) {
                $recentlyClosedParentAction = $this->popMountedTreeNodeItemAction();

                if (
                    blank($parentActionToCancelTo) ||
                    ($recentlyClosedParentAction === $parentActionToCancelTo)
                ) {
                    break;
                }
            }
        }

        if (! count($this->mountedTreeNodeItemActions)) {
            if ($shouldCloseModal) {
                $this->closeTreeNodeItemActionModal();
            }

            // Reset the action record if the action is being unmounted.
            if ($action instanceof TreeNodeAction) {
                $action->itemKey(null);
            } else {
                $action?->record(null);
            }
            // Reset the action record if the action is being unmounted.
            $this->mountedTreeNodeItemActionRecord(null);

            // Setting these to `null` creates a bug where the properties are
            // actually set to `'null'` strings and remain in the URL.
            $this->defaultTreeNodeItemAction = [];
            $this->defaultTreeNodeItemActionArguments = [];
            $this->defaultTreeNodeItemActionRecord = [];

            return;
        }

        $this->cacheMountedTreeNodeItemActionForm();

        $this->resetErrorBag();

        $this->openTreeNodeItemActionModal();
    }

    public function getMountedTreeNodeItemAction(): null | Action | TreeNodeAction
    {
        if (! count($this->mountedTreeNodeItemActions ?? [])) {
            return null;
        }

        if (($treeNode = $this->getTreeNode()) instanceof \SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer) {
            return $treeNode->getAction($this->mountedTreeNodeItemActions);
        }

        return null;
    }

    public function mountedTreeNodeItemActionShouldOpenModal(null | Action | TreeNodeAction $mountedAction = null): bool
    {
        return ($mountedAction ?? $this->getMountedTreeNodeItemAction())->shouldOpenModal(
            checkForFormUsing: $this->mountedTreeNodeItemActionHasForm(...),
        );
    }

    public function mountedTreeNodeItemActionRecord(int | string | null $itemKey): void
    {
        $this->mountedTreeNodeItemActionRecord = $itemKey;
    }

    public function getMountedTreeNodeItemActionRecord(): int | string | null
    {
        return $this->mountedTreeNodeItemActionRecord;
    }

    protected function cacheMountedTreeNodeItemActionForm(null | Action | TreeNodeAction $mountedAction = null): void
    {
        $this->cacheForm(
            'mountedTreeNodeItemActionForm',
            fn () => $this->getMountedTreeNodeItemActionForm($mountedAction),
        );
    }

    public function mountedTreeNodeItemActionHasForm(null | Action | TreeNodeAction $mountedAction = null): bool
    {
        return (bool) count($this->getMountedTreeNodeItemActionForm(mountedAction: $mountedAction)?->getComponents() ?? []);
    }

    public function getMountedTreeNodeItemActionForm(null | Action | TreeNodeAction $mountedAction = null): ?Form
    {
        $mountedAction ??= $this->getMountedTreeNodeItemAction();

        if (! $mountedAction) {
            return null;
        }

        if ((! $this->isCachingForms) && $this->hasCachedForm('mountedTreeNodeItemActionForm')) {
            return $this->getForm('mountedTreeNodeItemActionForm');
        }

        return $mountedAction->getForm(
            $this->makeForm()
                ->statePath('mountedTreeNodeItemActionsData.' . array_key_last($this->mountedTreeNodeItemActionsData))
                ->operation(implode('.', $this->mountedTreeNodeItemActions)),
        );
    }

    //region Helpers
    /**
     * Remove the last the mounted tree node item action.
     *
     * @return string|null The name of the mounted tree node item action, or null if none is mounted.
     */
    protected function popMountedTreeNodeItemAction(): ?string
    {
        try {
            return array_pop($this->mountedTreeNodeItemActions);
        } finally {
            array_pop($this->mountedTreeNodeItemActionsData);
        }
    }

    /**
     * Resets the properties related to the mounted tree node item action.
     *
     * This method is used to clear or reset any properties that are associated
     * with the currently mounted tree node item action, ensuring that the state
     * is clean and ready for the next action.
     */
    protected function resetMountedTreeNodeItemActionProperties(): void
    {
        $this->mountedTreeNodeItemActions = [];
        $this->mountedTreeNodeItemActionsArguments = [];
        $this->mountedTreeNodeItemActionsData = [];
    }

    protected function closeTreeNodeItemActionModal(): void
    {
        $this->dispatch('close-modal', id: $this->getTreeNodeItemModalId());
    }

    /**
     * Opens the modal for tree node item actions.
     *
     * This method is responsible for displaying the modal that allows users
     * to perform actions on a tree node item within the application.
     */
    protected function openTreeNodeItemActionModal(): void
    {
        $this->dispatch('open-modal', id: $this->getTreeNodeItemModalId());
    }

    protected function getTreeNodeItemModalId(): string
    {
        return "{$this->getId()}-treenodeitem-action";
    }
    //endregion Modal
}
