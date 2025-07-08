
function mediaItem({
    livewireKey,
}) {
    return {
        livewireKey,
        isDragOver: false,
        isDragging: false,

        onDragStart(event) {
            this.isDragging = true;
            const item = this.$el;
            // this.$dispatch('item-drag-start', { item });
            event.dataTransfer.setData('draggable-id', item.getAttribute('data-draggable-id'));
            event.dataTransfer.effectAllowed = 'move';
        },

        onDragEnd(event) {
            this.isDragging = false;
            // this.$dispatch('item-drag-end');
        },

        onDragOver(event) {
            event.preventDefault();
            this.isDragOver = true;
            event.dataTransfer.dropEffect = 'move';
        },

        onDragLeave(event) {
            // Only hide if we're actually leaving the element
            if (!event.currentTarget.contains(event.relatedTarget)) {
                this.isDragOver = false;
            }
        },

        onDrop(event) {

            const draggedItemId = event.dataTransfer.getData('draggable-id');
            const targetFolderId = this.$el.getAttribute('data-draggable-id');

            event.preventDefault();
            this.isDragOver = false;
            
            try {
                if (draggedItemId !== targetFolderId) {
                    Livewire.dispatch('moveMediaItem', { 
                        livewireKey: this.livewireKey, 
                        targetId: draggedItemId, 
                        toId: targetFolderId 
                    });
                } else {
                    console.warn('Cannot move item to itself:', {
                        'sourceId': draggedItemId,
                        'targetId': targetFolderId
                    });
                }
            } catch (error) {
                console.error('Error parsing dragged item:', error);
            }
        }
    };
}

// Global event listeners
document.addEventListener('alpine:init', () => {
    Alpine.data('mediaItem', mediaItem);
    // Alpine.store('dragDrop', {
    //     draggedItem: null
    // });
});

// // Handle global drag and drop events
// document.addEventListener('item-drag-start', (event) => {
//     Alpine.store('dragDrop').draggedItem = event.detail.item;
// });

// document.addEventListener('item-drag-end', () => {
//     Alpine.store('dragDrop').draggedItem = null;
// });
