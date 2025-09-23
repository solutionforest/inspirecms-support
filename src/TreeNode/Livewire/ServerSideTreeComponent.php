<?php

namespace SolutionForest\InspireCms\Support\TreeNode\Livewire;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use SolutionForest\InspireCms\Support\TreeNode\Concerns\WithServerSideTreeActions;

class ServerSideTreeComponent extends Component implements HasActions, HasForms
{
    use InteractsWithActions {
        InteractsWithActions::resolveAction as protected resolveBaseAction;
    }
    use InteractsWithForms;
    use WithServerSideTreeActions;

    protected static int $maxDepth = -1;
    protected static bool $showNodeActions = true;
    protected static bool $showToolbarActions = true;
    protected static bool $showNavigationHeader = true;
    protected static bool $enableNodeUrls = false;
    protected static bool $enableSelection = false;

    public array $nodes = [];

    public array $expandedNodes = [];

    public array $loadedChildrenCache = []; // Cache loaded children by parent ID

    public array $visibleNodes = []; // Currently visible nodes in the tree

    public array $selectedNodes = []; // Selected node IDs

    public ?string $startNodeId = null;
    public bool $multipleSelection = true;
    public bool $showOnlyRootItems = false; // New property for filtering
    public ?int $maxSelections = null; // Maximum number of selections allowed (null = unlimited)

    public function mount()
    {
        $this->loadRootNodes();
        // Initialize visible nodes with root nodes only
        $this->rebuildVisibleNodes();
    }

    public function loadRootNodes(): void
    {
        // Override this method in your implementation
        // Load root level nodes or nodes from the start node
        $this->nodes = $this->getRootNodes();
    }

    public function expandNode(string $nodeId): void
    {
        if (in_array($nodeId, $this->expandedNodes)) {
            return; // Already expanded
        }

        // Add to expanded nodes
        $this->expandedNodes[] = $nodeId;

        // Load children if not already cached
        if (! isset($this->loadedChildrenCache[$nodeId])) {
            $children = $this->getChildNodes($nodeId);
            $this->loadedChildrenCache[$nodeId] = $children;
        }

        // Rebuild visible nodes tree
        $this->rebuildVisibleNodes();
    }

    public function collapseNode(string $nodeId): void
    {
        $key = array_search($nodeId, $this->expandedNodes);
        if ($key !== false) {
            unset($this->expandedNodes[$key]);
            $this->expandedNodes = array_values($this->expandedNodes);

            // Also collapse all descendant nodes
            $this->collapseDescendants($nodeId);

            // Rebuild visible nodes tree
            $this->rebuildVisibleNodes();
        }
    }

    protected function collapseDescendants(string $parentId): void
    {
        // Get cached children if available
        $children = $this->loadedChildrenCache[$parentId] ?? [];
        foreach ($children as $child) {
            $this->collapseNode($child['id']);
        }
    }

    public function toggleNode(string $nodeId): void
    {
        if ($this->isNodeExpanded($nodeId)) {
            $this->collapseNode($nodeId);
        } else {
            $this->expandNode($nodeId);
        }
    }

    public function isNodeExpanded(string $nodeId): bool
    {
        return in_array($nodeId, $this->expandedNodes);
    }

    public function getChildrenForNode(string $parentId): array
    {
        // Return cached children if available
        return $this->loadedChildrenCache[$parentId] ?? [];
    }

    protected function rebuildVisibleNodes(): void
    {
        $this->visibleNodes = [];
        $this->addNodesToVisible($this->nodes, 0);
    }

    protected function addNodesToVisible(array $nodes, int $depth): void
    {
        foreach ($nodes as $node) {
            // Add current node
            $node['depth'] = $depth;
            $this->visibleNodes[] = $node;

            // Add children if node is expanded and has cached children
            if ($this->isNodeExpanded($node['id']) && isset($this->loadedChildrenCache[$node['id']])) {
                $this->addNodesToVisible($this->loadedChildrenCache[$node['id']], $depth + 1);
            }
        }
    }

    protected function getRootNodes(): array
    {
        // Return array of root nodes
        // Each node should have: id, name, icon, has_children, parent_id, depth
        return [];
    }

    protected function getChildNodes(string $parentId): array
    {
        // Return array of child nodes for the given parent
        return [];
    }

    protected function getNodeItemActions(): array
    {
        return [];
    }

    protected function getToolbarActions(): array
    {
        return [];
    }

    protected function getNavigationHeaderActions(): array
    {
        return [];
    }

    #[Renderless]
    public function getNodeItemActionsHtml($id)
    {
        $actions = $this->getNodeItemActions();

        return collect($actions)
            ->map(fn (Action | ActionGroup $action) => $action->toHtml())
            ->all();
    }

    public function getNodeLabel($node): array
    {
        return [
            'title' => $node['name'] ?? $node['title'] ?? 'Untitled',
            'description' => $node['description'] ?? null,
        ];
    }

    public function refreshTree(): void
    {
        $this->expandedNodes = [];
        $this->loadedChildrenCache = [];
        $this->loadRootNodes();
        $this->rebuildVisibleNodes();
    }

    public function expandAll(): void
    {
        // This would be expensive for large trees, so we'll implement it progressively
        $this->expandAllNodes($this->nodes);
        $this->rebuildVisibleNodes();
    }

    protected function expandAllNodes(array $nodes): void
    {
        foreach ($nodes as $node) {
            if ($node['has_children'] ?? false) {
                $this->expandNode($node['id']);

                // Recursively expand children
                $children = $this->getChildrenForNode($node['id']);
                $this->expandAllNodes($children);
            }
        }
    }

    public function collapseAll(): void
    {
        $this->expandedNodes = [];
        $this->rebuildVisibleNodes();
    }

    // Selection methods
    public function selectNode(string $nodeId): void
    {
        if (! $this->enableSelection) {
            return;
        }

        if ($this->multipleSelection) {
            if (! in_array($nodeId, $this->selectedNodes)) {
                // Check if we've reached the selection limit
                if ($this->maxSelections !== null && count($this->selectedNodes) >= $this->maxSelections) {
                    // Don't add if limit is reached
                    return;
                }
                $this->selectedNodes[] = $nodeId;
            }
        } else {
            $this->selectedNodes = [$nodeId];
        }
    }

    public function deselectNode(string $nodeId): void
    {
        if (! $this->enableSelection) {
            return;
        }

        $key = array_search($nodeId, $this->selectedNodes);
        if ($key !== false) {
            unset($this->selectedNodes[$key]);
            $this->selectedNodes = array_values($this->selectedNodes);
        }
    }

    public function toggleNodeSelection(string $nodeId): void
    {
        if ($this->isNodeSelected($nodeId)) {
            $this->deselectNode($nodeId);
        } else {
            $this->selectNode($nodeId);
        }
    }

    public function isNodeSelected(string $nodeId): bool
    {
        return in_array($nodeId, $this->selectedNodes);
    }

    public function clearSelection(): void
    {
        $this->selectedNodes = [];
    }

    public function getSelectedNodes(): array
    {
        return $this->selectedNodes;
    }

    public function canSelectMoreNodes(): bool
    {
        if (! $this->enableSelection || ! $this->multipleSelection) {
            return false;
        }

        if ($this->maxSelections === null) {
            return true; // No limit
        }

        return count($this->selectedNodes) < $this->maxSelections;
    }

    public function getSelectionLimit(): ?int
    {
        return $this->maxSelections;
    }

    public function getSelectionCount(): int
    {
        return count($this->selectedNodes);
    }

    public function getRemainingSelections(): ?int
    {
        if ($this->maxSelections === null) {
            return null; // Unlimited
        }

        return max(0, $this->maxSelections - count($this->selectedNodes));
    }

    public function getSelectedNodeData(): array
    {
        $selectedData = [];

        // Search in visible nodes
        foreach ($this->visibleNodes as $node) {
            if (in_array($node['id'], $this->selectedNodes)) {
                $selectedData[] = $node;
            }
        }

        // Search in cached children for any missing nodes
        foreach ($this->loadedChildrenCache as $children) {
            foreach ($children as $node) {
                if (in_array($node['id'], $this->selectedNodes) && ! in_array($node, $selectedData)) {
                    $selectedData[] = $node;
                }
            }
        }

        return $selectedData;
    }

    // URL handling
    public function getNodeUrl(array $node): ?string
    {
        return $node['url'] ?? null;
    }

    public function shouldRenderNodeAsLink(array $node): bool
    {
        return $this->enableNodeUrls && ! empty($this->getNodeUrl($node));
    }

    public function canSelectNode(string $nodeId): bool
    {
        if (! $this->enableSelection) {
            return false;
        }

        // If already selected, we can always deselect
        if ($this->isNodeSelected($nodeId)) {
            return true;
        }

        // For single selection, we can always select (it will replace current)
        if (! $this->multipleSelection) {
            return true;
        }

        // For multiple selection, check the limit
        return $this->canSelectMoreNodes();
    }

    // Action handling for tree nodesx

    protected function resolveAction(array $action, array $parentActions): ?Action
    {
        if (isset($action['arguments']['node'])) {
            return $this->resolveTreeNodeAction($action, $parentActions);
        }

        return $this->resolveBaseAction($action, $parentActions);
    }

    protected function resolveTreeNodeAction(array $action, array $parentActions): ?Action
    {
        return $this->resolveBaseAction($action, $parentActions);
    }

    protected function getHomeButtonText(): string|Htmlable
    {
        return 'Root';
    }

    protected function getIndexUrl(): string
    {
        // Override this method to return the appropriate index URL
        return '/';
    }

    protected function viewData()
    {
        // Only show root nodes - children are handled recursively by the template
        $rootNodes = array_filter($this->visibleNodes, function ($node) {
            return empty($node['parent_id']);
        });

        return [
            'nodes' => array_values($rootNodes),
            'rootNodesCount' => count($rootNodes),
            'multipleSelection' => $this->multipleSelection,
            'maxSelections' => $this->maxSelections,

            'homeButtonText' => $this->getHomeButtonText(),
            'indexUrl' => $this->getIndexUrl(),

            'toolbarActions' => static::$showToolbarActions ? $this->getToolbarActions() : [],
            'navigationHeaderActions' => static::$showNavigationHeader ? $this->getNavigationHeaderActions() : [],
            
            'enableNodeUrls' => static::$enableNodeUrls,
            'enableSelection' => static::$enableSelection,
            'showNodeActions' => static::$showNodeActions,
            'showToolbarActions' => static::$showToolbarActions,
            'showNavigationHeader' => static::$showNavigationHeader,
        ];
    }

    public function render()
    {
        return view('inspirecms-support::livewire.components.tree-node.service-side-tree', $this->viewData());
    }
}
