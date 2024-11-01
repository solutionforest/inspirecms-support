<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Contracts;

use Filament\Actions\Action;
use Filament\Forms\Form;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action as TreeNodeAction;

interface HasTreeNodeActions
{
    public function callMountedTreeNodeItemAction(array $arguments = []): mixed;

    public function mountTreeNodeItemAction(string $name, int | string | null $itemKey, array $arguments = []): mixed;

    public function unmountTreeNodeItemAction(bool $shouldCancelParentActions = true, bool $shouldCloseModal = true): void;

    public function getMountedTreeNodeItemAction(): null | Action | TreeNodeAction;

    public function mountedTreeNodeItemActionShouldOpenModal(null | Action | TreeNodeAction $mountedAction = null): bool;

    public function mountedTreeNodeItemActionRecord(int | string | null $itemKey): void;

    public function getMountedTreeNodeItemActionRecord(): int | string | null;

    public function mountedTreeNodeItemActionHasForm(null | Action | TreeNodeAction $mountedAction = null): bool;

    public function getMountedTreeNodeItemActionForm(null | Action | TreeNodeAction $mountedAction = null): ?Form;
}
