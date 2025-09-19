<?php

namespace SolutionForest\InspireCms\Support\TreeNode\Livewire;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;
use SolutionForest\InspireCms\Support\TreeNode\Concerns\WithSortableTreeActions;

class SortableTreeComponent extends Component implements HasActions, HasForms
{
    use InteractsWithActions {
        InteractsWithActions::resolveAction as protected traitResolveAction;
    }
    use InteractsWithForms;
    use WithSortableTreeActions;
    
    protected static bool $searchable = false;
    protected static bool $haveToolbarActions = false;
    protected static bool $allowDragDrop = false;
    public int $maxDepth = -1;
    public int $maxVisibleDepth = 20;

    public array $nodes = [];

    public function mount()
    {
        $this->refreshNodes();
    }

    protected function getToolbarActions(): array
    {
        return [
            ActionGroup::make([

                Action::make('expandAll')
                    ->icon(Heroicon::ArrowsPointingOut)
                    ->color('gray')
                    ->alpineClickHandler('expandAll()'),

                Action::make('collapseAll')
                    ->icon(Heroicon::ArrowsPointingIn)
                    ->color('gray')
                    ->alpineClickHandler('collapseAll()'),

            ])->buttonGroup(),

            Action::make('saveOrder')
                ->icon(Heroicon::Check)
                ->color('primary')
                ->action('saveOrder')
                ->disabled(fn () => empty($this->nodes)),

            Action::make('resetTree')
                ->label(__('inspirecms::general.reset_tree'))
                ->icon(Heroicon::ArrowPath)
                ->color('secondary')
                ->iconButton()
                ->action('refreshNodes'),
        ];
    }

    protected function getNodeItemActions(): array
    {
        return [];
    }

    public function saveOrder()
    {
        // Implement the logic to save the order of nodes after drag-and-drop

    }

    public function refreshNodes()
    {
        // Implement the logic to refresh the nodes, e.g., fetch from database
        // $this->nodes = ...;
    }

    //region Action Handling
    public function mountRecursiveTreeNodeAction(string $name, $treeNodeId, array $arguments = [], array $context = []): mixed
    {
        $context['recordKey'] = $treeNodeId;
        $context['recursiveTreeNode'] = true;

        return $this->mountAction($name, $arguments, $context);
    } 

    protected function resolveAction(array $action, array $parentActions): ?Action
    {
        if (filled($action['context']['recursiveTreeNode'] ?? null)) {
            return $this->resolveRecursiveTreeNodeAction($action, $parentActions);
        }

        return $this->traitResolveAction($action, $parentActions);
    }

    protected function resolveRecursiveTreeNodeAction(array $action, array $parentActions): ?Action
    {
        return $this->traitResolveAction($action, $parentActions);
    }
    //endregion Action Handling

    public function render()
    {
        $toolbarActions = $this->getToolbarActions();
        $nodeItemActions = $this->getNodeItemActions();

        return view('inspirecms-support::livewire.components.tree-node.sortable-tree', [
            'allowDragDrop' => static::$allowDragDrop,
            'isSearchable' => static::$searchable,
            'haveToolbarActions' => static::$haveToolbarActions && count($toolbarActions) > 0,
            'toolbarActions' => $toolbarActions,
            'nodeItemActions' => $nodeItemActions,
        ]);
    }
}
