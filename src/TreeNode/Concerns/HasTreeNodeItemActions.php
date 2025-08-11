<?php

namespace SolutionForest\InspireCms\Support\TreeNode\Concerns;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Exceptions\ActionNotResolvableException;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Livewire\Attributes\Url;
use SolutionForest\InspireCms\Support\TreeNode\Actions\Action as TreeNodeAction;
use SolutionForest\InspireCms\Support\TreeNode\Actions\ActionGroup as TreeNodeActionGroup;
use SolutionForest\InspireCms\Support\TreeNode\Contracts\HasTreeNode;
use SolutionForest\InspireCms\Support\TreeNode\ModelExplorer\Concerns\HasActions;

trait HasTreeNodeItemActions
{
    use InteractsWithActions {
        resolveAction as baseResolveAction;
    }

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

    public function bootedHasTreeNodeItemActions(): void
    {
        if ($this instanceof HasTreeNode) {
            foreach ($this->getTreeNode()?->getFlatActions() ?? [] as $action) {
                $this->configureSelectedModelItemFormAction($action);
                $this->cacheAction($action);
            }
        }
    }

    protected function configureSelectedModelItemFormAction(Action | TreeNodeAction $action): void {}

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function callMountedTreeNodeItemAction(array $arguments = []): mixed
    {
        return $this->callMountedAction($arguments);
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function mountTreeNodeItemAction(string $name, int | string | null $itemKey, array $arguments = []): mixed
    {
        return $this->mountAction($name, $arguments, context: [
            'treeNode' => true,
            'recordKey' => $itemKey,
        ]);
    }

    public function unmountTreeNodeItemAction(bool $shouldCancelParentActions = true, bool $shouldCloseModal = true): void
    {
        $this->unmountAction(
            $shouldCancelParentActions,
            $shouldCloseModal,
        );
    }

    public function getMountedTreeNodeItemAction(): null | Action | TreeNodeAction
    {
        return $this->getMountedAction();
    }

    public function mountedTreeNodeItemActionShouldOpenModal(null | Action | TreeNodeAction $mountedAction = null): bool
    {
        return $this->mountedActionShouldOpenModal($mountedAction);
    }

    public function mountedTreeNodeItemActionRecord(int | string | null $itemKey): void
    {
        $this->mountedTreeNodeItemActionRecord = $itemKey;
    }

    public function getMountedTreeNodeItemActionRecord(): int | string | null
    {
        return $this->mountedTreeNodeItemActionRecord;
    }

    /**
     * @deprecated Use `mountedActionHasSchema()` instead.
     */
    public function mountedTreeNodeItemActionHasForm(null | Action | TreeNodeAction $mountedAction = null): bool
    {
        return $this->mountedActionHasSchema($mountedAction);
    }

    /**
     * @deprecated Use `getMountedActionSchema()` instead.
     */
    public function getMountedTreeNodeItemActionForm(null | Action | TreeNodeAction $mountedAction = null): ?Schema
    {
        return $this->getMountedActionSchema(0, $mountedAction);
    }

    /**
     * @param  array<string, mixed>  $action
     * @param  array<Action>  $parentActions
     */
    protected function resolveAction(array $action, array $parentActions): ?Action
    {
        if ($this instanceof HasTreeNode && ($action['context']['treeNode'] ?? null)) {
            return $this->resolveTreeNodeAction($action, $parentActions);
        }

        return $this->baseResolveAction($action, $parentActions);
    }

    /**
     * @param  array<string, mixed>  $action
     * @param  array<Action>  $parentActions
     */
    protected function resolveTreeNodeAction(array $action, array $parentActions): ?Action
    {
        if (! ($this instanceof HasTreeNode)) {
            throw new ActionNotResolvableException('Failed to resolve tree node action for Livewire component without the [' . HasTreeNode::class . '] trait.');
        }

        $resolvedAction = null;

        if (count($parentActions)) {
            $parentAction = Arr::last($parentActions);
            $resolvedAction = $parentAction->getModalAction($action['name']) ?? throw new ActionNotResolvableException("Action [{$action['name']}] was not found for action [{$parentAction->getName()}].");
        } else {
            $treeNode = $this->getTreeNode();
            if (! in_array(HasActions::class, class_uses_recursive($treeNode))) {
                throw new ActionNotResolvableException("Action [{$action['name']}] not found on tree node.");
            }
            $resolvedAction = $treeNode->getAction($action['name']) ?? throw new ActionNotResolvableException("Action [{$action['name']}] not found on tree node.");
        }

        if (filled($action['context']['recordKey'] ?? null)) {
            $record = $action['context']['recordKey'];

            $targetAction = $resolvedAction->getRootGroup() ?? $resolvedAction;
            if ($targetAction instanceof TreeNodeAction || $targetAction instanceof TreeNodeActionGroup) {
                $targetAction->itemKey($record);
            } elseif ($targetAction instanceof Action) {
                $targetAction->record($record);
            }
        }

        return $resolvedAction;
    }
}
