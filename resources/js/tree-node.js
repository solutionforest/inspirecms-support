const TreeNode = ({
    expanded,
    selected,
}) => {
    return {
        
        expanded,

        selected,

        init () {
            this.expanded = this.expanded || [];
            this.selected = this.selected || [];
            this.$watch('expanded', (value) => {
                // Ensure expanded is always an array
                this.expanded = Array.isArray(value) ? value : (Array.from(value) || []);
            });
            this.$watch('selected', (value) => {
                // Ensure selected is always an array
                this.selected = Array.isArray(value) ? value : (Array.from(value) || []);
            });
        },

        toggleItem(key) {
            if (this.expanded.includes(key)) {
                this.expanded = this.expanded.filter(item => item !== key);
            } else {
                this.expanded.push(key);
            }
        },

        selectItem(key) {
            if (this.selected.includes(key)) {
                this.selected = this.selected.filter(item => item !== key);
            } else {
                this.selected.push(key);
            }
        },

        isExpanded(key) {
            return this.expanded && Array.from(this.expanded).includes(key);
        },

        isSelected(key) {
            return this.selected && Array.from(this.selected).includes(key);
        },
    }
}

const TreeView = (config = {}) => {
    // Default configuration options
    const defaults = {
        data: [],
        idField: 'id',
        nameField: 'name',
        childrenField: 'children',
        expandedField: 'expanded',
        maxDepth: -1, // -1 for unlimited logical depth
        maxVisibleDepth: 4, // Default set to 4 levels for UI rendering depth
        allowDragDrop: true,
        allowRearrange: true,
        allowCrossCategory: true, // New option to allow cross-category movement
        showActions: true,
        nodeTemplate: null,
        onNodeSelect: () => {},
        onNodeMove: () => {},
        onNodeAdd: () => {},
        onNodeEdit: () => {},
        onNodeDelete: () => {},
        onTreeUpdate: () => {},
        searchPlaceholder: 'Search nodes...',
        highlightSearch: true,
        onSearch: () => {},
        searchDebounceMs: 300,
        enableKeyboardNav: true,
        onKeyboardNav: () => {},
        // Performance settings
        updateDebounceMs: 50, // Time to debounce UI updates
        useDeepCloning: false, // Whether to use deep cloning (performance impact)
    };

    // Merge defaults with user-provided config
    const options = { ...defaults, ...config };

    const formatTreeData = (data) => {
        return data || [];
        // if (Array.isArray(data)) {
        //     return data;
        // }
        // // Fallback to empty array if data is not valid
        // return [];
    }

    return {
        // Core data
        treeData: formatTreeData(options.data),
        jsonOutput: '',
        
        // UI state
        selectedNode: null,
        draggedNodeId: null,
        dropTargetIndex: null,
        dropTargetParent: null,
        showNodeModal: false,
        editingNodeId: null,
        nodeForm: {
            name: '',
            parentId: ''
        },
        showJsonModal: false,
        jsonInput: '',
        copied: false,
        dropPosition: 'inside', // Can be 'before', 'after', or 'inside'
        pathMap: {}, // Stores full paths to nodes for easier parent-child validation
        searchQuery: '',
        searchResults: [],
        lastSearchTime: null,
        searchDebounceTimeout: null,
        lastFocusedNode: null,
        updateTreeDebounceTimeout: null, // Added to prevent UI freezing
        isUpdating: false, // Flag to prevent concurrent updates
        
        /**
         * Initialize the component - with updated initialization
         */
        init() {
            this.updateJsonOutput();
            
            // Ensure all nodes have children arrays
            this.ensureChildrenArrays();
            
            // Update max visible depth attributes on all nodes
            this.updateNodeVisibility();
            
            // Build path map
            this.buildPathMap();
            this.updateJsonOutput();
            
            // Dispatch initialization event
            this.$dispatch('treeview:initialized', { tree: this.treeData });

            // Initialize search state
            this.searchQuery = '';
            this.searchResults = [];
            this.$watch('searchQuery', (value) => this.handleSearch(value));

            // Set up keyboard navigation
            if (options.enableKeyboardNav) {
                this.setupKeyboardNavigation();
            }
            
            // Force node expansion on visibility changes
            this.watchNodesVisibility();
        },
        
        /**
         * Update node visibility based on maxVisibleDepth
         */
        updateNodeVisibility() {
            const maxDepth = this.getMaxVisibleDepth();
            
            // Function to set visibility attributes on nodes
            const setVisibilityAttributes = (nodes, currentDepth = 1) => {
                nodes.forEach(node => {
                    // Set visibility attribute on the node
                    node._visible = currentDepth <= maxDepth;
                    
                    // Process children recursively
                    if (node[options.childrenField] && node[options.childrenField].length > 0) {
                        setVisibilityAttributes(node[options.childrenField], currentDepth + 1);
                    }
                });
            };
            
            // Start the process at the root level
            setVisibilityAttributes(this.treeData);
        },
        
        /**
         * Watch for X-show visibility changes and ensure nodes are properly displayed
         */
        watchNodesVisibility() {
            // Reduced operation frequency to prevent freezing
            setTimeout(() => {
                const treeContainers = document.querySelectorAll('.tree-container');
                
                if (!treeContainers.length) return;
                
                // Use only one observer for better performance
                const observer = new MutationObserver((mutations) => {
                    // Throttle updates
                    if (this.isUpdating) return;
                    
                    let needsUpdate = false;
                    for (const mutation of mutations) {
                        if (mutation.type === 'attributes' && 
                            (mutation.attributeName === 'style' || 
                            mutation.attributeName === 'class')) {
                            
                            const nodeId = mutation.target.id?.replace('node-', '');
                            if (nodeId) {
                                const node = this.findNodeById(nodeId);
                                if (node && node[options.expandedField]) {
                                    needsUpdate = true;
                                    break;
                                }
                            }
                        }
                    }
                    
                    if (needsUpdate) {
                        // Debounce the actual update to avoid freezing
                        if (this.updateTreeDebounceTimeout) {
                            clearTimeout(this.updateTreeDebounceTimeout);
                        }
                        
                        this.updateTreeDebounceTimeout = setTimeout(() => {
                            this.updateNodeVisibility();
                            // Use a shallow clone for better performance
                            this.treeData = [...this.treeData];
                            this.isUpdating = false;
                        }, 100); // Longer debounce for observer events
                        
                        this.isUpdating = true;
                    }
                });
                
                // Observe all containers with simplified options
                treeContainers.forEach(container => {
                    observer.observe(container, { 
                        attributes: true,
                        attributeFilter: ['style', 'class']
                    });
                });
            }, 1000); // Longer initial delay
        },
        
        /**
         * Force tree to redraw fully - optimized to prevent freezing
         */
        redrawTree() {
            // Prevent excessive redraws
            if (this.isUpdating) return;
            
            this.isUpdating = true;
            
            // Clear any pending update
            if (this.updateTreeDebounceTimeout) {
                clearTimeout(this.updateTreeDebounceTimeout);
            }
            
            // Update visibility attributes before redrawing
            this.updateNodeVisibility();
            
            // Debounce the update to prevent UI freezing
            this.updateTreeDebounceTimeout = setTimeout(() => {
                // Make a shallow copy instead of deep copy when possible
                if (options.useDeepCloning) {
                    this.treeData = JSON.parse(JSON.stringify(this.treeData));
                } else {
                    // Using a shallow clone is much more performant
                    this.treeData = [...this.treeData];
                }
                
                this.isUpdating = false;
            }, options.updateDebounceMs);
        },
        
        /**
         * Build a map of paths to nodes for validation
         */
        buildPathMap() {
            this.pathMap = {};
            
            const buildPaths = (nodes, currentPath = []) => {
                nodes.forEach(node => {
                    const nodePath = [...currentPath, node[options.idField]];
                    this.pathMap[node[options.idField]] = nodePath;
                    
                    if (node[options.childrenField] && node[options.childrenField].length > 0) {
                        buildPaths(node[options.childrenField], nodePath);
                    }
                });
            };
            
            buildPaths(this.treeData);
        },
        
        /**
         * Get flattened nodes for select dropdowns
         */
        get flattenedNodes() {
            const result = [];
            
            const flatten = (nodes, level = 0) => {
                nodes.forEach(node => {
                    result.push({ 
                        id: node[options.idField], 
                        name: node[options.nameField], 
                        level 
                    });
                    
                    if (node[options.childrenField] && node[options.childrenField].length > 0) {
                        flatten(node[options.childrenField], level + 1);
                    }
                });
            };
            
            flatten(this.treeData);
            return result;
        },
        
        /**
         * Find node by ID
         */
        findNodeById(id, nodes = this.treeData) {
            for (const node of nodes) {
                if (node[options.idField] === id) {
                    return node;
                }
                
                if (node[options.childrenField] && node[options.childrenField].length > 0) {
                    const found = this.findNodeById(id, node[options.childrenField]);
                    if (found) return found;
                }
            }
            
            return null;
        },
        
        /**
         * Find parent node
         */
        findParentNode(childId, nodes = this.treeData) {
            for (const node of nodes) {
                if (node[options.childrenField] && 
                    node[options.childrenField].some(child => child[options.idField] === childId)) {
                    return node;
                }
                
                if (node[options.childrenField] && node[options.childrenField].length > 0) {
                    const found = this.findParentNode(childId, node[options.childrenField]);
                    if (found) return found;
                }
            }
            
            return null;
        },
        
        /**
         * Select a node
         */
        selectNode(id) {
            this.selectedNode = id;
            const node = this.findNodeById(id);
            if (node) {
                options.onNodeSelect(node);
                this.$dispatch('treeview:node-selected', { node });
            }
        },
        
        /**
         * Toggle node expanded state - enhanced to handle empty children arrays
         */
        toggleNode(id) {
            const toggleExpanded = (nodes) => {
                for (let i = 0; i < nodes.length; i++) {
                    if (nodes[i][options.idField] === id) {
                        // Make sure node has a children array
                        if (!nodes[i][options.childrenField]) {
                            nodes[i][options.childrenField] = [];
                        }
                        
                        nodes[i][options.expandedField] = !nodes[i][options.expandedField];
                        return true;
                    }
                    
                    if (nodes[i][options.childrenField] && nodes[i][options.childrenField].length > 0) {
                        if (toggleExpanded(nodes[i][options.childrenField])) {
                            return true;
                        }
                    }
                }
                
                return false;
            };
            
            toggleExpanded(this.treeData);
        },
        
        /**
         * Remove a node by ID
         */
        removeNodeById(id, nodes = this.treeData) {
            for (let i = 0; i < nodes.length; i++) {
                if (nodes[i][options.idField] === id) {
                    const removedNode = nodes.splice(i, 1)[0];
                    options.onNodeDelete(removedNode);
                    this.$dispatch('treeview:node-deleted', { node: removedNode });
                    return true;
                }
                
                if (nodes[i][options.childrenField] && nodes[i][options.childrenField].length > 0) {
                    if (this.removeNodeById(id, nodes[i][options.childrenField])) {
                        return true;
                    }
                }
            }
            
            return false;
        },
        
        /**
         * Show modal to add a new node
         */
        addNode() {
            this.showNodeModal = true;
            this.editingNodeId = null;
            this.nodeForm = {
                name: '',
                parentId: ''
            };
        },
        
        /**
         * Show modal to edit an existing node
         */
        editNode(id) {
            const node = this.findNodeById(id);
            if (node) {
                this.editingNodeId = id;
                this.nodeForm = {
                    name: node[options.nameField],
                    parentId: ''
                };
                this.showNodeModal = true;
            }
        },
        
        /**
         * Save node (add new or update existing)
         */
        saveNode() {
            if (!this.nodeForm.name.trim()) {
                alert('Node name cannot be empty');
                return;
            }
            
            if (this.editingNodeId) {
                // Edit existing node
                const node = this.findNodeById(this.editingNodeId);
                if (node) {
                    const oldName = node[options.nameField];
                    node[options.nameField] = this.nodeForm.name;
                    
                    options.onNodeEdit(node, { oldName });
                    this.$dispatch('treeview:node-edited', { 
                        node, 
                        oldName 
                    });
                }
            } else {
                // Add new node
                const newNodeId = `node-${Date.now()}`;
                
                // Calculate depth for visibility
                const parentId = this.nodeForm.parentId;
                const nodeDepth = parentId ? this.getNodeDepth(parentId) + 1 : 1;
                
                const newNode = {
                    [options.idField]: newNodeId,
                    [options.nameField]: this.nodeForm.name,
                    [options.expandedField]: true,
                    [options.childrenField]: [],
                    _visible: nodeDepth <= this.getMaxVisibleDepth() // Set visibility attribute
                };
                
                if (this.nodeForm.parentId) {
                    // Add as a child to the selected parent
                    const parent = this.findNodeById(this.nodeForm.parentId);
                    if (parent) {
                        if (!parent[options.childrenField]) {
                            parent[options.childrenField] = [];
                        }
                        parent[options.childrenField].push(newNode);
                        parent[options.expandedField] = true; // Ensure parent is expanded
                        options.onNodeAdd(newNode, parent);
                        this.$dispatch('treeview:node-added', { node: newNode, parent });
                    }
                } else {
                    // Add to top level
                    this.treeData.push(newNode);
                    options.onNodeAdd(newNode, null);
                    this.$dispatch('treeview:node-added', { node: newNode, parent: null });
                }

                // Update path map and rebuild visibility information
                this.buildPathMap();
                this.updateNodeVisibility();
            }
            
            this.showNodeModal = false;
            this.updateJsonOutput();
            
            // Force a tree update
            this.redrawTree();
        },
        
        /**
         * Delete a node and its children
         */
        deleteNode(id) {
            if (confirm('Are you sure you want to delete this node and all its children?')) {
                this.removeNodeById(id);
                this.buildPathMap();
                this.updateJsonOutput();
            }
        },
        
        /**
         * Handle drag start
         */
        dragStart(event, id) {
            if (!options.allowDragDrop) return;
            
            this.draggedNodeId = id;
            event.dataTransfer.setData('text/plain', id);
            event.target.classList.add('dragging');
        },
        
        /**
         * Handle drag end
         */
        dragEnd() {
            document.querySelectorAll('.dragging').forEach(el => {
                el.classList.remove('dragging');
            });
            document.querySelectorAll('.dragover, .dragover-before, .dragover-after').forEach(el => {
                el.classList.remove('dragover', 'dragover-before', 'dragover-after');
            });
            this.dropTargetIndex = null;
            this.dropTargetParent = null;
            this.dropPosition = 'inside';
        },
        
        /**
         * Handle drag leave - Fixes the "dragLeave is not defined" error 
         */
        dragLeave(event) {
            // Remove all drag-related styling classes from the target element
            event.currentTarget.classList.remove('dragover', 'dragover-before', 'dragover-after');
        },
        
        /**
         * Handle drag over - Enhanced to better handle dropping on nodes
         */
        dragOver(event, index, parentId) {
            if (!options.allowDragDrop) return;
            
            event.preventDefault();
            
            // Skip if dragging over itself or we're trying to drag into a non-container
            if (this.draggedNodeId === parentId) {
                return;
            }
            
            // Get the target node
            const targetNode = parentId === null 
                ? this.treeData[index] 
                : this.findNodeById(parentId)?.children?.[index];
            
            if (!targetNode && parentId !== null) {
                // If target node doesn't exist but parentId is provided,
                // we might be dragging to an empty children array
                const parentNode = this.findNodeById(parentId);
                if (!parentNode) return;
                
                // Make sure parent is expanded to allow dropping
                if (!parentNode[options.expandedField]) {
                    parentNode[options.expandedField] = true;
                }
            }
            
            // Get the target element's rectangle
            const rect = event.currentTarget.getBoundingClientRect();
            const mouseY = event.clientY;
            const nodeHeight = rect.height;
            
            // Determine if we're dropping before, after, or inside the node
            // Top 25% - before, bottom 25% - after, middle 50% - inside
            const relativeMousePos = mouseY - rect.top;
            const percentY = relativeMousePos / nodeHeight;
            
            // For leaf nodes, favor before/after over inside
            const targetHasChildren = targetNode && 
                                        targetNode[options.childrenField] && 
                                        targetNode[options.childrenField].length > 0;

            // Always allow dropping inside for all nodes, including Twitter
            if (percentY >= 0.25 && percentY <= 0.75) {
                // Inside the node
                this.dropPosition = 'inside';
                event.currentTarget.classList.add('dragover');
                event.currentTarget.classList.remove('dragover-before', 'dragover-after');
            } else if (percentY < 0.25) {
                // Before the node
                this.dropPosition = 'before';
                event.currentTarget.classList.add('dragover-before');
                event.currentTarget.classList.remove('dragover-after', 'dragover');
            } else {
                // After the node
                this.dropPosition = 'after';
                event.currentTarget.classList.add('dragover-after');
                event.currentTarget.classList.remove('dragover-before', 'dragover');
            }
            
            this.dropTargetIndex = index;
            this.dropTargetParent = parentId;
        },
        
        /**
         * Check if the potential move would create a cycle in the tree
         */
        wouldCreateCycle(draggedNodeId, targetParentId) {
            // If dragging to root level, no cycle possible
            if (targetParentId === null) return false;
            
            // If targetParentId is undefined, assume no cycle
            if (targetParentId === undefined) return false;
            
            // Get path to target parent
            const parentPath = this.pathMap[targetParentId];
            
            // If parent path includes the dragged node, this would create a cycle
            return parentPath && parentPath.includes(draggedNodeId);
        },
        
        /**
         * Is this node from a different category?
         * This check prevents nodes from "Marketing" being moved into "Sales" 
         * and vice versa, for legacy reasons.
         */
        isFromDifferentCategory(draggedNodeId, targetParentId) {
            // If cross-category moves are allowed, always return false
            if (options.allowCrossCategory) return false;
            
            // If moving to root level, always allowed
            if (targetParentId === null) return false;
            
            // Get the topmost parent of both nodes
            const draggedTopParent = this.getTopmostParent(draggedNodeId);
            const targetTopParent = this.getTopmostParent(targetParentId);
            
            // If either is at root level, allow the move
            if (!draggedTopParent || !targetTopParent) return false;
            
            // If they're from different top-level parents, don't allow cross-category moves
            return draggedTopParent !== targetTopParent;
        },
        
        /**
         * Get the topmost parent ID of a node
         */
        getTopmostParent(nodeId) {
            const path = this.pathMap[nodeId];
            // If path doesn't exist or node is at root level
            if (!path || path.length <= 1) return null;
            // Return the first ID in the path (topmost parent)
            return path[0];
        },
        
        /**
         * Check if the move would exceed maximum depth
         */
        wouldExceedMaxDepth(draggedNodeId, targetParentId) {
            // If maxDepth is -1, then unlimited depth is allowed
            if (options.maxDepth === -1) return false;
            
            // If dropping at root level, depth is always 1
            if (targetParentId === null) return false;
            
            // Get the depth of the target parent
            const targetDepth = this.getNodeDepth(targetParentId);
            
            // Get the maximum depth of the dragged subtree
            const draggedSubtreeDepth = this.getSubtreeDepth(draggedNodeId);
            
            // Check if the combined depth would exceed maxDepth
            return (targetDepth + draggedSubtreeDepth) > options.maxDepth;
        },
        
        /**
         * Get the depth of a node in the tree (1-based)
         */
        getNodeDepth(nodeId) {
            const path = this.pathMap[nodeId];
            return path ? path.length : 0;
        },
        
        /**
         * Get the maximum visible depth for UI rendering 
         */
        getMaxVisibleDepth() {
            // Ensure maxVisibleDepth is at least 1 and a valid number
            return Math.max(1, options.maxVisibleDepth || 4);
        },

        /**
         * Check if the node exceeds the visible depth limit
         */
        exceedsVisibleDepth(nodeId) {
            const depth = this.getNodeDepth(nodeId);
            return depth > this.getMaxVisibleDepth();
        },

        /**
         * Check if a node should be visually shown based on depth
         */
        isNodeVisible(node) {
            // If node has visibility attribute, use it
            if (node._visible !== undefined) {
                return node._visible;
            }
            
            // Fallback to calculating it
            if (node[options.idField]) {
                const nodeDepth = this.getNodeDepth(node[options.idField]);
                return nodeDepth <= this.getMaxVisibleDepth();
            }
            
            return true; // Default to visible
        },
        
        /**
         * Get the maximum depth of a subtree
         */
        getSubtreeDepth(nodeId) {
            const node = this.findNodeById(nodeId);
            if (!node || !node[options.childrenField] || node[options.childrenField].length === 0) {
                return 1; // Just the node itself
            }
            
            // Find the max depth of all children
            let maxChildDepth = 0;
            for (const child of node[options.childrenField]) {
                const childDepth = this.getSubtreeDepth(child[options.idField]);
                maxChildDepth = Math.max(maxChildDepth, childDepth);
            }
            
            return 1 + maxChildDepth; // This node plus the deepest child path
        },
        
        /**
         * Handle drop event - Updated to fix visibility issues with deep nesting
         */
        drop(event, index, parentId) {
            event.preventDefault();
            
            if (!options.allowDragDrop) return;
            if (!this.draggedNodeId) return;
            if (this.isUpdating) return; // Prevent concurrent operations
            
            // Set the updating flag to prevent concurrent operations
            this.isUpdating = true;
            
            const draggedNode = this.findNodeById(this.draggedNodeId);
            if (!draggedNode) {
                this.isUpdating = false;
                return;
            }
            
            // Store key information to restore later
            const wasExpanded = draggedNode[options.expandedField] === true;
            
            // Original parent of the dragged node
            const originalParentNode = this.findParentNode(this.draggedNodeId);
            
            // Calculate target position based on drop position
            let targetParentId = null;
            let targetIndex = 0;
            
            // Get the target node for reference
            const targetNode = parentId === null 
                ? this.treeData[index] 
                : (parentId && index !== undefined ? this.findNodeById(parentId)?.children?.[index] : null);
            
            if (this.dropPosition === 'inside') {
                // Dropping inside a node - make it a child
                targetParentId = parentId === null && targetNode ? targetNode.id : parentId;
                
                // Handle dropping inside Twitter or any other node specifically
                if (targetNode) {
                    targetParentId = targetNode.id;
                    
                    // Initialize children array if it doesn't exist
                    if (!targetNode[options.childrenField]) {
                        targetNode[options.childrenField] = [];
                    }
                    
                    targetIndex = targetNode[options.childrenField].length;
                    
                    // Make sure target is expanded
                    targetNode[options.expandedField] = true;
                } else {
                    // Dropping inside a parent node with no specific target
                    const targetParent = this.findNodeById(targetParentId);
                    if (targetParent) {
                        if (!targetParent[options.childrenField]) {
                            targetParent[options.childrenField] = [];
                        }
                        targetIndex = targetParent[options.childrenField].length;
                        targetParent[options.expandedField] = true;
                    } else {
                        targetIndex = 0;
                    }
                }
            } else {
                // Dropping before or after a node
                if (parentId === null) {
                    // Target is at root level
                    targetParentId = null;
                    targetIndex = index;
                    if (this.dropPosition === 'after') targetIndex++;
                } else {
                    // Target is inside another node
                    targetParentId = parentId;
                    const targetParent = this.findNodeById(parentId);
                    if (targetParent) {
                        if (!targetParent[options.childrenField]) {
                            targetParent[options.childrenField] = [];
                        }
                        targetParent[options.expandedField] = true;
                    }
                    targetIndex = index;
                    if (this.dropPosition === 'after') targetIndex++;
                }
            }
            
            // Calculate depth to see if we need to respect maxVisibleDepth
            let targetDepth = targetParentId === null ? 1 : this.getNodeDepth(targetParentId) + 1;
            
            // Handle maxVisibleDepth constraint for visualization purposes
            if (targetDepth > this.getMaxVisibleDepth()) {
                console.log(`Dropping at depth ${targetDepth} which exceeds maxVisibleDepth ${this.getMaxVisibleDepth()}`);
                // Node will still be added but we need to handle visualization
                // Allow the drop but mark the node for special visibility handling
            }
            
            // Validation checks
            if (this.wouldCreateCycle(this.draggedNodeId, targetParentId)) {
                console.log("Cannot move a node into its own descendant");
                this.dragEnd();
                this.isUpdating = false;
                return;
            }
            
            if (options.maxDepth !== -1 && this.wouldExceedMaxDepth(this.draggedNodeId, targetParentId)) {
                console.log("Cannot exceed maximum depth");
                this.dragEnd();
                this.isUpdating = false;
                return;
            }
            
            // Use clone conditionally (better performance)
            const nodeCopy = options.useDeepCloning 
                ? JSON.parse(JSON.stringify(draggedNode))
                : { ...draggedNode };
            
            // Make sure expanded state persists
            nodeCopy[options.expandedField] = wasExpanded;
            
            // Set visibility attribute based on depth
            nodeCopy._visible = targetDepth <= this.getMaxVisibleDepth();
            
            // Remove node from its original position
            if (originalParentNode) {
                const idx = originalParentNode[options.childrenField].findIndex(
                    n => n[options.idField] === this.draggedNodeId
                );
                if (idx !== -1) {
                    // Adjust target index if needed when moving within the same parent
                    if (targetParentId === originalParentNode[options.idField] && idx < targetIndex) {
                        targetIndex--;
                    }
                    originalParentNode[options.childrenField].splice(idx, 1);
                }
            } else {
                const idx = this.treeData.findIndex(n => n[options.idField] === this.draggedNodeId);
                if (idx !== -1) {
                    // Adjust target index if needed when moving within root level
                    if (targetParentId === null && idx < targetIndex) {
                        targetIndex--;
                    }
                    this.treeData.splice(idx, 1);
                }
            }
            
            // Add node at its new position
            if (targetParentId === null) {
                // Adding to root level
                this.treeData.splice(targetIndex, 0, nodeCopy);
            } else {
                // Adding as a child
                const newParent = this.findNodeById(targetParentId);
                if (newParent) {
                    if (!newParent[options.childrenField]) {
                        newParent[options.childrenField] = [];
                    }
                    newParent[options.childrenField].splice(targetIndex, 0, nodeCopy);
                    // Make sure parent is expanded
                    newParent[options.expandedField] = true;
                }
            }
            
            // Reset drag state
            this.dragEnd();
            
            // Rebuild path map and update output
            this.buildPathMap();
            this.updateNodeVisibility(); // Update visibility of all nodes
            this.updateJsonOutput();
            
            // Notify event handlers
            const moveDetails = {
                node: nodeCopy,
                oldParent: originalParentNode,
                newParentId: targetParentId,
                newIndex: targetIndex,
                position: this.dropPosition
            };
            
            options.onNodeMove(nodeCopy, moveDetails);
            
            // Performance fix: Don't use deep cloning for the entire tree redraw
            // Debounce the update to prevent UI freezing
            if (this.updateTreeDebounceTimeout) {
                clearTimeout(this.updateTreeDebounceTimeout);
            }
            
            this.updateTreeDebounceTimeout = setTimeout(() => {
                // Make sure all parent nodes are expanded
                if (targetParentId !== null) {
                    const expandParentPath = (id) => {
                        const parent = this.findParentNode(id);
                        if (!parent) return;
                        parent[options.expandedField] = true;
                        expandParentPath(parent[options.idField]);
                    };
                    
                    expandParentPath(targetParentId);
                    
                    const targetParent = this.findNodeById(targetParentId);
                    if (targetParent) {
                        targetParent[options.expandedField] = true;
                    }
                }
                
                // Dispatch event only after we've finished processing
                this.$dispatch('treeview:node-moved', moveDetails);
                
                // Reset the updating flag
                this.isUpdating = false;
            }, options.updateDebounceMs);
        },
        
        /**
         * Ensure all nodes have children arrays
         */
        ensureChildrenArrays() {
            const ensure = (nodes) => {
                nodes.forEach(node => {
                    if (!node[options.childrenField]) {
                        node[options.childrenField] = [];
                    }
                    if (node[options.childrenField].length > 0) {
                        ensure(node[options.childrenField]);
                    }
                });
            };
            ensure(this.treeData);
        },
        
        /**
         * Update JSON output
         */
        updateJsonOutput() {
            this.jsonOutput = JSON.stringify(this.treeData, null, 2);
        },
        
        /**
         * Handle search input
         */
        handleSearch(query) {
            clearTimeout(this.searchDebounceTimeout);
            this.searchDebounceTimeout = setTimeout(() => {
                this.searchResults = this.searchTree(query);
                options.onSearch(query, this.searchResults);
            }, options.searchDebounceMs);
        },
        
        /**
         * Search the tree for nodes matching the query
         */
        searchTree(query) {
            const results = [];
            
            const search = (nodes, parentPath = []) => {
                nodes.forEach(node => {
                    const nodePath = [...parentPath, node[options.idField]];
                    if (node[options.nameField].toLowerCase().includes(query.toLowerCase())) {
                        results.push({ node, path: nodePath });
                    }
                    if (node[options.childrenField] && node[options.childrenField].length > 0) {
                        search(node[options.childrenField], nodePath);
                    }
                });
            };
            
            search(this.treeData);
            return results;
        },
        
        /**
         * Set up keyboard navigation
         */
        setupKeyboardNavigation() {
            this.$el.addEventListener('keydown', (event) => {
                if (!this.selectedNode) return;
                
                const key = event.key;
                const node = this.findNodeById(this.selectedNode);
                if (!node) return;
                
                switch (key) {
                    case 'ArrowUp':
                        this.navigateUp(node);
                        break;
                    case 'ArrowDown':
                        this.navigateDown(node);
                        break;
                    case 'ArrowLeft':
                        this.collapseNode(node);
                        break;
                    case 'ArrowRight':
                        this.expandNode(node);
                        break;
                    case 'Enter':
                        this.selectNode(node[options.idField]);
                        break;
                    default:
                        break;
                }
                
                options.onKeyboardNav(key, node);
            });
        },
        
        /**
         * Navigate up in the tree
         */
        navigateUp(node) {
            const path = this.pathMap[node[options.idField]];
            if (!path) return;
            
            const parentId = path[path.length - 2];
            if (!parentId) return;
            
            const parent = this.findNodeById(parentId);
            if (!parent) return;
            
            const index = parent[options.childrenField].findIndex(n => n[options.idField] === node[options.idField]);
            if (index > 0) {
                const prevNode = parent[options.childrenField][index - 1];
                this.selectNode(prevNode[options.idField]);
            } else {
                this.selectNode(parent[options.idField]);
            }
        },
        
        /**
         * Navigate down in the tree
         */
        navigateDown(node) {
            if (node[options.childrenField] && node[options.childrenField].length > 0) {
                this.selectNode(node[options.childrenField][0][options.idField]);
            } else {
                const path = this.pathMap[node[options.idField]];
                if (!path) return;
                
                const parentId = path[path.length - 2];
                if (!parentId) return;
                
                const parent = this.findNodeById(parentId);
                if (!parent) return;
                
                const index = parent[options.childrenField].findIndex(n => n[options.idField] === node[options.idField]);
                if (index < parent[options.childrenField].length - 1) {
                    const nextNode = parent[options.childrenField][index + 1];
                    this.selectNode(nextNode[options.idField]);
                } else {
                    const grandParentId = path[path.length - 3];
                    if (!grandParentId) return;
                    
                    const grandParent = this.findNodeById(grandParentId);
                    if (!grandParent) return;
                    
                    const grandParentIndex = grandParent[options.childrenField].findIndex(n => n[options.idField] === parent[options.idField]);
                    if (grandParentIndex < grandParent[options.childrenField].length - 1) {
                        const nextNode = grandParent[options.childrenField][grandParentIndex + 1];
                        this.selectNode(nextNode[options.idField]);
                    }
                }
            }
        },
        
        /**
         * Collapse a node
         */
        collapseNode(node) {
            if (node[options.expandedField]) {
                node[options.expandedField] = false;
            } else {
                const path = this.pathMap[node[options.idField]];
                if (!path) return;
                
                const parentId = path[path.length - 2];
                if (!parentId) return;
                
                this.selectNode(parentId);
            }
        },
        
        /**
         * Expand a node
         */
        expandNode(node) {
            if (!node[options.expandedField]) {
                node[options.expandedField] = true;
            } else if (node[options.childrenField] && node[options.childrenField].length > 0) {
                this.selectNode(node[options.childrenField][0][options.idField]);
            }
        },

        /**
         * Check if a node matches the current search
         */
        nodeMatchesSearch(node) {
            if (!this.searchQuery || !this.searchQuery.trim()) return false;
            return this.searchResults.some(r => r.id === node[options.idField]);
        },
        
        /**
         * Get highlight ranges for search text
         */
        getHighlightRanges(text) {
            if (!this.searchQuery || !options.highlightSearch || !this.searchQuery.trim()) return [];
            
            const query = this.searchQuery.toLowerCase();
            const textLower = text.toLowerCase();
            const ranges = [];
            let pos = 0;
            
            while ((pos = textLower.indexOf(query, pos)) !== -1) {
                ranges.push([pos, pos + query.length]);
                pos += query.length;
            }
            
            return ranges;
        },

        /**
         * Format node text with search highlighting
         */
        formatNodeText(node) {
            const text = node[options.nameField];
            
            // If search highlighting is disabled or there's no search query, return plain text
            if (!options.highlightSearch || !this.searchQuery.trim()) {
                return text;
            }
            
            const ranges = this.getHighlightRanges(text);
            
            if (ranges.length === 0) return text;
            
            let result = '';
            let lastPos = 0;
            
            ranges.forEach(([start, end]) => {
                result += text.slice(lastPos, start);
                result += `<span class="search-highlight">${text.slice(start, end)}</span>`;
                lastPos = end;
            });
            
            result += text.slice(lastPos);
            return result;
        },
        
        /**
         * Expand paths to search results
         */
        expandSearchResults() {
            const expandPath = (nodePath) => {
                let current = this.treeData;
                
                for (let i = 0; i < nodePath.length - 1; i++) {
                    const segment = nodePath[i];
                    const found = current.find(
                        n => n[options.nameField] === segment
                    );
                    
                    if (found) {
                        found[options.expandedField] = true;
                        current = found[options.childrenField] || [];
                    }
                }
            };

            this.searchResults.forEach(result => expandPath(result.path));
        },

        /**
         * Check if a node should be rendered in the UI
         * This function can be called from the template to render or hide nodes
         */
        shouldRenderNode(node, depth) {
            // Always render if it's within maxVisibleDepth
            if (depth <= this.getMaxVisibleDepth()) {
                return true;
            }
            
            // Check the node's visibility attribute
            if (node._visible !== undefined) {
                return node._visible;
            }
            
            // If the node has an ID, calculate based on depth
            if (node[options.idField]) {
                const nodeDepth = this.getNodeDepth(node[options.idField]);
                return nodeDepth <= this.getMaxVisibleDepth();
            }
            
            // Default fallback - don't render if depth exceeds maxVisibleDepth
            return false;
        },

        /**
         * Expand all nodes in the tree
         */
        expandAll() {
            const expand = (nodes) => {
                nodes.forEach(n => {
                    n[options.expandedField] = true;
                    if (n[options.childrenField]?.length) {
                        expand(n[options.childrenField]);
                    }
                });
            };
            expand(this.treeData);
            this.redrawTree();
        },

        /**
         * Collapse all nodes in the tree
         */
        collapseAll() {
            const collapse = (nodes) => {
                nodes.forEach(n => {
                    n[options.expandedField] = false;
                    if (n[options.childrenField]?.length) {
                        collapse(n[options.childrenField]);
                    }
                });
            };
            collapse(this.treeData);
            this.redrawTree();
        },
    };
};

document.addEventListener('alpine:init', () => {
    Alpine.data('TreeNode', TreeNode);
    Alpine.data('TreeView', TreeView);
});

